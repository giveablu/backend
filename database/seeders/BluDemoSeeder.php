<?php

namespace Database\Seeders;

use App\Models\AppFaq;
use App\Models\BankDetail;
use App\Models\Donation;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BluDemoSeeder extends Seeder
{
    /**
     * Seed a representative set of Blu demo data for tests and local previews.
     */
    public function run(): void
    {
        $this->seedSettings();
        $donor = $this->seedDonor();
        $receiver = $this->seedReceiver();
        $post = $this->seedReceiverPost($receiver);
        $this->seedDonation($donor, $post);
        $this->seedFaqs();
    }

    private function seedSettings(): void
    {
        Setting::query()->updateOrCreate(
            ['id' => 1],
            [
                'default_amount' => 20,
                'app_version' => '1.0.0',
                'app_feature' => implode('|', [
                    'Discover verified receiver stories',
                    'Donate in seconds with preset amounts',
                    'Receive instant impact notifications'
                ]),
            ]
        );
    }

    private function seedDonor(): User
    {
        return User::query()->updateOrCreate(
            ['email' => 'donor@blu.test'],
            [
                'name' => 'Blu Test Donor',
                'password' => Hash::make('password'),
                'role' => 'donor',
                'phone' => '+15550000001',
                'email_verified_at' => now()->subMonths(2),
                'phone_verified_at' => now()->subMonths(2),
                'device_token' => Str::uuid()->toString(),
            ]
        );
    }

    private function seedReceiver(): User
    {
        return User::query()->updateOrCreate(
            ['email' => 'receiver@blu.test'],
            [
                'name' => 'Amina K.',
                'password' => Hash::make('password'),
                'role' => 'receiver',
                'phone' => '+15550000042',
                'gender' => 'female',
                'email_verified_at' => now()->subMonth(),
                'phone_verified_at' => now()->subMonth(),
                'photo' => 'images/receiver-home.png',
            ]
        );
    }

    private function seedReceiverPost(User $receiver): Post
    {
        $post = Post::query()->updateOrCreate(
            ['user_id' => $receiver->id],
            [
                'amount' => '5000',
                'paid' => '1200',
                'biography' => 'Amina leads a neighborhood food program that supports 80 families every week.',
                'image' => 'images/receiver-home.png',
            ]
        );

        $tagNames = [
            'Community Support',
            'Food Security',
            'Education Fund'
        ];

        $tagIds = collect($tagNames)->map(function (string $name) {
            return Tag::query()->firstOrCreate(['name' => $name])->id;
        });

        $post->tags()->sync($tagIds);

        BankDetail::query()->updateOrCreate(
            ['user_id' => $receiver->id],
            [
                'bank_name' => 'Blu Community Bank',
                'account_name' => 'Amina K',
                'account_no' => '000111222333',
                'ifsc_code' => 'BLU00042',
            ]
        );

        return $post;
    }

    private function seedDonation(User $donor, Post $post): void
    {
        Donation::query()->updateOrCreate(
            [
                'post_id' => $post->id,
                'user_id' => $donor->id,
            ],
            [
                'gross_amount' => 200,
                'processing_fee' => 0,
                'platform_fee' => 0,
                'net_amount' => 200,
                'currency' => 'USD',
                'processor_payload' => null,
                'activity' => false,
            ]
        );

        $post->update(['paid' => '1400']);
    }

    private function seedFaqs(): void
    {
        $faqs = [
            [
                'type' => 'donor',
                'question' => 'How are recipients verified?',
                'answer' => 'Each story is reviewed by the Blu team and backed by local organizations before appearing on the platform.',
            ],
            [
                'type' => 'donor',
                'question' => 'Can I track my impact?',
                'answer' => 'Yes, donors receive email summaries and can review detailed donation history inside their account.',
            ],
            [
                'type' => 'receiver',
                'question' => 'How quickly are funds available?',
                'answer' => 'Withdraw requests are processed within 2 business days once your bank information is verified.',
            ],
        ];

        foreach ($faqs as $faq) {
            AppFaq::query()->updateOrCreate(
                [
                    'type' => $faq['type'],
                    'question' => $faq['question'],
                ],
                ['answer' => $faq['answer']]
            );
        }
    }
}
