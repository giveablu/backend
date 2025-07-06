<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ForgotPassController extends Controller
{
    public function forgotPass(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'response' => false,
                    'message' => $validator->errors()->all(),
                ], 400);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'response' => false,
                    'message' => ['User not found with this email address.'],
                ], 404);
            }

            // Generate 6-digit OTP as string
            $otp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = Carbon::now()->addMinutes(15);
        
            Log::info('Generated OTP', [
                'email' => $user->email,
                'otp_length' => strlen($otp),
                'expires_at' => $expiresAt->toDateTimeString(),
            ]);

            // Clear any existing OTP first
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'reset_otp' => null,
                    'reset_otp_expires_at' => null,
                    'reset_token' => null,
                    'reset_token_expires_at' => null,
                ]);

            // Store new OTP
            $updated = DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'reset_otp' => $otp,
                    'reset_otp_expires_at' => $expiresAt,
                    'updated_at' => Carbon::now(),
                ]);

            // Verify storage immediately
            $verification = DB::table('users')
                ->where('id', $user->id)
                ->select('reset_otp', 'reset_otp_expires_at')
                ->first();

            Log::info('OTP Storage Verification', [
                'update_result' => $updated,
                'stored_otp_exists' => !empty($verification->reset_otp) ? 'YES' : 'NO',
                'stored_expires_at' => $verification->reset_otp_expires_at ?? 'NULL',
                'original_otp_length' => strlen($otp),
                'match' => ($verification->reset_otp ?? '') === $otp ? 'YES' : 'NO',
            ]);

            if (!$updated || ($verification->reset_otp ?? '') !== $otp) {
                throw new \Exception('Failed to store OTP correctly in database');
            }

            // Send email
            try {
                Mail::to($user->email)->send(new ResetPasswordMail($otp, $user->email, $user->id, true));
                Log::info('OTP Email Sent Successfully', ['email' => $user->email]);
            } catch (\Exception $mailError) {
                Log::error('Email Send Failed', ['error' => $mailError->getMessage()]);
            }

            return response()->json([
                'response' => true,
                'message' => ['Reset code sent to your email address.'],
                'data' => [
                    'message' => 'Reset code sent successfully',
                    'email' => $user->email,
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Forgot Password Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'response' => false,
                'message' => ['Something went wrong: ' . $e->getMessage()],
            ], 500);
        }
    }

    public function verifyResetOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
                'otp' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::warning('OTP Validation Failed', [
                    'errors' => $validator->errors()->all(),
                    'request_data' => $request->only(['email', 'otp'])
                ]);
                return response()->json([
                    'response' => false,
                    'message' => $validator->errors()->all(),
                ], 400);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'response' => false,
                    'message' => ['User not found.'],
                ], 404);
            }

            // Get fresh data from database
            $freshUser = DB::table('users')
                ->where('id', $user->id)
                ->select('reset_otp', 'reset_otp_expires_at')
                ->first();

            // Clean the OTP inputs - remove any whitespace and ensure string
            $submittedOtp = trim((string)$request->otp);
            $storedOtp = trim((string)($freshUser->reset_otp ?? ''));

            Log::info('OTP Verification Debug', [
                'email' => $request->email,
                'user_id' => $user->id,
                'submitted_otp_length' => strlen($submittedOtp),
                'stored_otp_exists' => !empty($storedOtp) ? 'YES' : 'NO',
                'stored_otp_length' => strlen($storedOtp),
                'expires_at' => $freshUser->reset_otp_expires_at ?? 'NULL',
                'current_time' => Carbon::now()->toDateTimeString(),
                'exact_match' => $storedOtp === $submittedOtp ? 'YES' : 'NO',
            ]);

            // Check if OTP exists
            if (empty($storedOtp)) {
                Log::warning('No OTP Found', [
                    'email' => $request->email,
                    'user_id' => $user->id
                ]);
                return response()->json([
                    'response' => false,
                    'message' => ['No reset code found. Please request a new one.'],
                ], 400);
            }

            // Check expiration
            if ($freshUser->reset_otp_expires_at && Carbon::now()->isAfter($freshUser->reset_otp_expires_at)) {
                Log::warning('OTP Expired', [
                    'email' => $request->email,
                    'expires_at' => $freshUser->reset_otp_expires_at,
                    'current_time' => Carbon::now()->toDateTimeString()
                ]);
                return response()->json([
                    'response' => false,
                    'message' => ['Reset code has expired. Please request a new one.'],
                ], 400);
            }

            // Multiple comparison methods to ensure we catch the match
            $exactMatch = $storedOtp === $submittedOtp;
            $numericMatch = (int)$storedOtp === (int)$submittedOtp;
            $paddedMatch = str_pad($submittedOtp, 6, '0', STR_PAD_LEFT) === str_pad($storedOtp, 6, '0', STR_PAD_LEFT);

            Log::info('OTP Comparison Results', [
                'exact_match' => $exactMatch,
                'numeric_match' => $numericMatch,
                'padded_match' => $paddedMatch,
            ]);

            if (!$exactMatch && !$numericMatch && !$paddedMatch) {
                Log::warning('All OTP Comparisons Failed', [
                    'email' => $request->email,
                    'stored_length' => strlen($storedOtp),
                    'submitted_length' => strlen($submittedOtp),
                ]);
                return response()->json([
                    'response' => false,
                    'message' => ['Invalid reset code. Please check and try again.'],
                ], 400);
            }

            // Generate reset token
            $resetToken = Str::random(60);
            $tokenExpiresAt = Carbon::now()->addMinutes(30);

            $tokenUpdated = DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'reset_token' => $resetToken,
                    'reset_token_expires_at' => $tokenExpiresAt,
                    'reset_otp' => null,
                    'reset_otp_expires_at' => null,
                    'updated_at' => Carbon::now(),
            ]);

            Log::info('OTP Verification Successful', [
                'email' => $request->email,
                'match_type' => $exactMatch ? 'exact' : ($numericMatch ? 'numeric' : 'padded'),
                'reset_token_created' => $tokenUpdated ? 'YES' : 'NO',
            ]);

            return response()->json([
                'response' => true,
                'message' => ['OTP verified successfully.'],
                'data' => [
                    'message' => 'OTP verified. You can now reset your password.',
                    'reset_token' => $resetToken,
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('OTP Verification Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'response' => false,
                'message' => ['Something went wrong: ' . $e->getMessage()],
            ], 500);
        }
    }

    public function resetPass(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
                'reset_token' => 'required|string',
                'password' => 'required|string|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'response' => false,
                    'message' => $validator->errors()->all(),
                ], 400);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'response' => false,
                    'message' => ['User not found.'],
                ], 404);
            }

            // Check if reset token is valid and hasn't expired
            if ($user->reset_token !== $request->reset_token) {
                return response()->json([
                    'response' => false,
                    'message' => ['Invalid reset token.'],
                ], 400);
            }

            if (Carbon::now()->isAfter($user->reset_token_expires_at)) {
                return response()->json([
                    'response' => false,
                    'message' => ['Reset token has expired. Please start the process again.'],
                ], 400);
            }

            // Update password and clear reset tokens
            $user->update([
                'password' => Hash::make($request->password),
                'reset_token' => null,
                'reset_token_expires_at' => null,
            ]);

            Log::info('Password Reset Successful', ['email' => $request->email]);

            return response()->json([
                'response' => true,
                'message' => ['Password reset successfully.'],
                'data' => [
                    'message' => 'Your password has been reset successfully. You can now login with your new password.',
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Password Reset Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'response' => false,
                'message' => ['Something went wrong: ' . $e->getMessage()],
            ], 500);
        }
    }

    public function test()
    {
        return response()->json([
            'message' => 'This is the ForgotPassController - correct for password reset!',
            'controller' => 'ForgotPassController',
            'file' => __FILE__,
            'methods' => [
                'forgotPass' => 'POST /api/auth/forgot-password',
                'verifyResetOtp' => 'POST /api/auth/verify-reset-otp', 
                'resetPass' => 'POST /api/auth/reset-password'
            ]
        ]);
    }

    public function switchRole(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'role' => 'required|in:donor,receiver',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'response' => false,
                    'message' => $validator->errors()->all(),
                ], 400);
            }

            $user = $request->user();
            $user->role = $request->role;
            $user->save();

            Log::info('Role Switch Successful', [
                'user_id' => $user->id,
                'email' => $user->email,
                'old_role' => $user->getOriginal('role'),
                'new_role' => $request->role
            ]);

            return response()->json([
                'response' => true,
                'message' => ['Role updated successfully.'],
                'data' => $user
            ]);

        } catch (\Exception $e) {
            Log::error('Role Switch Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'response' => false,
                'message' => ['Something went wrong: ' . $e->getMessage()],
            ], 500);
        }
    }
}
