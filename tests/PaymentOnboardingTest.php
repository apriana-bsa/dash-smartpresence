<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class PaymentOnboardingTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * A basic test example.
     *
     * @return void
     */
     public function testNavbarPaymentVisible() {
       $user = factory(App\User::class)->make([
         'email' => 'dewa@gmail.com',
       ]);

       $this->actingAs($user)
            ->withSession([
              'onboardingstep' => 1,
              'enable_onboarding' => 1,
              'perusahaan_subscription' => 1,
              'perusahaan_jumlah_transaksi' => 0
            ])
            ->visit('/')
            ->see('+ Pembayaran');
     }

     public function testNavbarPaymentNotVisible() {
       $user = factory(App\User::class)->make([
         'email' => 'dewa@gmail.com',
       ]);

       $this->actingAs($user)
            ->withSession([
              'onboardingstep' => 1,
              'enable_onboarding' => 1,
              'perusahaan_subscription' => 0,
              'perusahaan_jumlah_transaksi' => 10
            ])
            ->visit('/')
            ->see('Beranda')
            ->dontSee('+ Pembayaran');
     }

     public function testUserKuotaVisible() {
       $user = factory(App\User::class)->make([
         'email' => 'dewa@gmail.com',
       ]);

       $this->actingAs($user)
            ->withSession([
              'onboardingstep' => 1,
              'enable_onboarding' => 1,
              'perusahaan_subscription' => 1,
              'perusahaan_jumlah_transaksi' => 0
            ])
            ->visit('/pembayaran')
            ->see('id="kuota_1bulan"')
            ->see('id="kuota_3bulan"')
            ->see('id="kuota_6bulan"')
            ->see('id="kuota_12bulan"');
     }

     public function testUserKuotaNotVisible() {
       $user = factory(App\User::class)->make([
         'email' => 'dewa@gmail.com',
       ]);

       $this->actingAs($user)
            ->withSession([
              'onboardingstep' => 1,
              'enable_onboarding' => 1,
              'perusahaan_subscription' => 1,
              'perusahaan_jumlah_transaksi' => 10
            ])
            ->visit('/pembayaran')
            ->dontSee('id="kuota_1bulan"')
            ->dontSee('id="kuota_3bulan"')
            ->dontSee('id="kuota_6bulan"')
            ->dontSee('id="kuota_12bulan"');
     }

     public function testPopUpChatOnPembayaranPage() {
       $user = factory(App\User::class)->make([
         'email' => 'dewa@gmail.com',
       ]);

       $this->actingAs($user)
            ->withSession([
              'onboardingstep' => 1,
              'enable_onboarding' => 1,
              'perusahaan_subscription' => 1,
              'perusahaan_jumlah_transaksi' => 0
            ])
            ->visit('/pembayaran')
            ->see('Jika Anda memiliki pertanyaan atau membutuhkan bantuan, silakan klik di sini untuk terhubung dengan tim kami.');
     }

     public function testPopUpChatNotOnPembayaranPage() {
       $user = factory(App\User::class)->make([
         'email' => 'dewa@gmail.com',
       ]);

       $this->actingAs($user)
            ->withSession([
              'onboardingstep' => 1,
              'enable_onboarding' => 1,
              'perusahaan_subscription' => 1,
              'perusahaan_jumlah_transaksi' => 0
            ])
            ->visit('/')
            ->see('Beranda')
            ->dontSee('Jika Anda memiliki pertanyaan atau membutuhkan bantuan, silakan klik di sini untuk terhubung dengan tim kami.');
     }

     public function testPopUpCongratOnPembayaranPage() {
      $user = factory(App\User::class)->make([
        'email' => 'dewa@gmail.com',
      ]);
      $this->actingAs($user)
           ->withSession([
             'conf_bahasaperusahaan' => 'id',
             'onboardingstep' => '6',
             'perusahaan_jumlah_transaksi' => '0',
             'perusahaan_subscription' => '1',
             'enable_onboarding' => '1'
           ])
           ->visit('/pembayaran')
           ->see('Anda baru saja menyelesaikan proses onboarding SmartPresence.');
    }

    public function testNotShowPopUpCongratOnPembayaranPage() {
      $user = factory(App\User::class)->make([
        'email' => 'dewa@gmail.com',
      ]);
      $this->actingAs($user)
           ->withSession([
             'conf_bahasaperusahaan' => 'id',
             'onboardingstep' => '7',
             'perusahaan_jumlah_transaksi' => '0',
             'perusahaan_subscription' => '1',
             'enable_onboarding' => '1'
           ])
           ->visit('/pembayaran')
           ->dontSee('Anda baru saja menyelesaikan proses onboarding SmartPresence.');
    }
}
