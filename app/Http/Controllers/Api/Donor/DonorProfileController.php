<?php

namespace App\Http\Controllers\Api\Donor;

use App\Models\User;
use Illuminate\Http\Request;
use App\Mail\UpdateEmailMail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Donor\DonorProfileResource;

class DonorProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware(['user.role:donor']);
    }

    public function index(Request $request)
    {
        return (new DonorProfileResource($request->user()))->additional([
            'response' => true,
            'message' => ['Donor Profile']
        ]);
    }

    public function update(Request $request)
    {
        $hardshipInput = $request->input('preferred_hardship_ids');
        if (is_string($hardshipInput)) {
            $decoded = json_decode($hardshipInput, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $request->merge(['preferred_hardship_ids' => $decoded]);
            }
        }

        $valid = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($request->user()->id)],
            'phone' => ['required', Rule::unique('users')->ignore($request->user()->id)],
            'photo' => ['nullable', 'image', 'max:10240'],
            'password' => ['nullable', Rule::requiredIf($request->filled('old_password')), 'confirmed', Rules\Password::defaults()],
            'old_password' => ['nullable', Rule::requiredIf($request->filled('password'))],
            'preferred_country' => ['nullable', 'string', 'max:150'],
            'preferred_region' => ['nullable', 'string', 'max:150'],
            'preferred_city' => ['nullable', 'string', 'max:150'],
            'preferred_hardship_ids' => ['nullable', 'array'],
            'preferred_hardship_ids.*' => ['integer', Rule::exists('tags', 'id')],
        ], [
            'photo.max' => 'Photo must be under 10MB'
        ]);

        if ($valid->fails()) {
            $message = collect([
                $valid->errors()->first('name'),
                $valid->errors()->first('email'),
                $valid->errors()->first('phone'),
                $valid->errors()->first('photo'),
                $valid->errors()->first('old_password'),
                $valid->errors()->first('password'),
                $valid->errors()->first('preferred_country'),
                $valid->errors()->first('preferred_region'),
                $valid->errors()->first('preferred_city'),
                $valid->errors()->first('preferred_hardship_ids'),
            ])->filter(fn ($item, $key) => !empty($item))->values();

            return response()->json(['response' => false, 'message' => $message]);
        } else {
            // update user details
            $user = $request->user();
            $user->update([
                'name' => $request->name,
                'phone' => $request->phone,
            ]);

            // update profile photo
            if (!is_null($request->file('photo'))) {
                if (!is_null($user->photo) && Storage::disk('public')->exists($user->photo)) {
                    Storage::disk('public')->delete($user->photo);
                }

                $path = $request->photo->store('profile/photo', 'public');

                $user->update(['photo' => $path]);
            }


            if ($request->filled('email') && $request->input('email') !== $user->email) {

                if (!$request->filled('mail_otp')) {
                    $otp = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
                    $userDetail = User::find($user->id);
                    $data = $userDetail->mailOtp()->updateOrCreate([
                        'user_id' => $userDetail->id,
                    ],[
                        'old_mail' => $userDetail->email,
                        'new_mail' =>$request->email,
                        'new_code' => $otp,
                        'expire' => now()->addMinutes(5)
                    ]);

                    Mail::to($request->email)->send(new UpdateEmailMail($data));
    
                    return response()->json([
                        'status' => 1,
                        'message' => 'OTP has been sent to your email.',
                    ], 200);
                }
    
                $response = $this->verifyOtp($user, $request->input('mail_otp'));
    
                // If OTP verification fails, return the response
                if ($response->getData()->status === 0) {
                    return $response;
                }
    
                // Update email after successful OTP verification
                $user->email = $request->input('email');
                $user->save();
            }

            // update password
            if (!is_null($request->old_password)) {
                if (!Hash::check($request->input('old_password'), $user->password)) {
                    return response()->json(['response' => false, 'message' => 'Check old password']);
                } else {
                    if (!is_null($request->old_password) && !is_null($request->password)) {
                        $user->update([
                            'password' => Hash::make($request->password),
                        ]);
                    }
                }
            }

            $normalizedCountry = $this->normalizeInputString($request->input('preferred_country'));
            $normalizedRegion = $this->normalizeInputString($request->input('preferred_region'));
            $normalizedCity = $this->normalizeInputString($request->input('preferred_city'));
            $normalizedHardshipIds = $this->normalizeHardshipIds($request->input('preferred_hardship_ids', []));

            if ($normalizedCountry || $normalizedRegion || $normalizedCity || !empty($normalizedHardshipIds)) {
                $user->donorPreference()->updateOrCreate([], [
                    'preferred_country' => $normalizedCountry,
                    'preferred_region' => $normalizedRegion,
                    'preferred_city' => $normalizedCity,
                    'preferred_hardship_ids' => !empty($normalizedHardshipIds) ? $normalizedHardshipIds : null,
                ]);
            } else {
                if ($user->donorPreference) {
                    $user->donorPreference->delete();
                }
            }

            // send data
            return (new DonorProfileResource($request->user()))->additional([
                'response' => true,
                'message' => ['Profile Updated Successfully']
            ]);
        }
    }


    protected function normalizeInputString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = is_string($value) ? $value : (string) $value;
        $trimmed = trim($string);

        return $trimmed === '' ? null : $trimmed;
    }

    protected function normalizeHardshipIds($value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return collect($value)
            ->filter(static fn ($entry) => is_int($entry) || (is_string($entry) && ctype_digit($entry)))
            ->map(static fn ($entry) => (int) $entry)
            ->unique()
            ->values()
            ->all();
    }


    protected function verifyOtp($user, $otp)
    {
        // Get the OTP record from the user's profile
        $otpRecord = $user->mailOtp;

        // Check if the profile exists and has an OTP code
        if (!empty($otpRecord)) {
            // Check if OTP matches the saved code
            if ($otp == $otpRecord->new_code) {
                // Check if OTP is still valid (within 5 minutes)
                $otpGeneratedAt = $otpRecord->expire_at;
                if ($otpGeneratedAt && now()->diffInMinutes($otpGeneratedAt) > 5) {
                    return response()->json([
                        'status' => 0,
                        'message' => 'OTP has expired. Please request a new one.',
                        'error_code' => 'EXPIRED_OTP'
                    ], 422);
                }
                return response()->json([
                    'status' => 1,
                    'message' => 'OTP verified successfully.',
                ], 200);
            }
        }

        // Return failure response
        return response()->json([
            'status' => 0,
            'message' => 'Invalid OTP.',
            'error_code' => 'INVALID_OTP',
        ], 422);
    }
}
