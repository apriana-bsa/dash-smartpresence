<?php

// use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OnboardingTest extends TestCase
{
    use DatabaseTransactions;

    // Setting this allows both DB connections to be reset between tests
    protected $connectionsToTransact = ['perusahaan_db'];
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }

    public function testOnboardingNavbar() {
      $user = factory(App\User::class)->make([
        'email' => 'dewa@gmail.com',
      ]);
      $this->actingAs($user)
           ->withSession([
             'conf_bahasaperusahaan' => 'id',
             'onboardingstep' => '1',
             'perusahaan_jumlah_transaksi' => '0',
             'enable_onboarding' => '1'
           ])
           ->visit('/')
           ->see('Panduan')
           ->see('+ Attribut');
    }

    public function testOnboardingNavbarStep1() {
      $user = factory(App\User::class)->make([
        'email' => 'dewa@gmail.com',
      ]);
      $this->actingAs($user)
           ->withSession([
             'conf_bahasaperusahaan' => 'id',
             'onboardingstep' => '1',
             'perusahaan_jumlah_transaksi' => '0',
             'enable_onboarding' => '1'
           ])
           ->visit('/')
           ->see('Langkah 1: Silakan isi bagian Atribut.');
    }

    public function testOnboardingNavbarStep2() {
      $user = factory(App\User::class)->make([
        'email' => 'dewa@gmail.com',
      ]);
      $this->actingAs($user)
           ->withSession([
             'conf_bahasaperusahaan' => 'id',
             'onboardingstep' => '2',
             'perusahaan_jumlah_transaksi' => '0',
             'enable_onboarding' => '1'
           ])
           ->visit('/')
           ->see('Langkah 2: Silakan isi jam kerja yang berlaku di perusahaan Anda.');
    }

    public function testOnboardingNavbarStep3() {
      $user = factory(App\User::class)->make([
        'email' => 'dewa@gmail.com',
      ]);
      $this->actingAs($user)
           ->withSession([
             'conf_bahasaperusahaan' => 'id',
             'onboardingstep' => '3',
             'perusahaan_jumlah_transaksi' => '0',
             'enable_onboarding' => '1'
           ])
           ->visit('/')
           ->see('Langkah 3: Silakan isi data pegawai.');
    }

    public function testOnboardingNavbarStep4() {
      $user = factory(App\User::class)->make([
        'email' => 'dewa@gmail.com',
      ]);
      $this->actingAs($user)
           ->withSession([
             'conf_bahasaperusahaan' => 'id',
             'onboardingstep' => '4',
             'perusahaan_jumlah_transaksi' => '0',
             'enable_onboarding' => '1'
           ])
           ->visit('/')
           ->see('Langkah 4: Silakan isi data jam kerja masing-masing pegawai.');
    }

    public function testOnboardingNavbarStep5() {
      $user = factory(App\User::class)->make([
        'email' => 'dewa@gmail.com',
      ]);
      $this->actingAs($user)
           ->withSession([
             'conf_bahasaperusahaan' => 'id',
             'onboardingstep' => '5',
             'perusahaan_jumlah_transaksi' => '0',
             'enable_onboarding' => '1'
           ])
           ->visit('/')
           ->see('Langkah 5: Silakan klik untuk menyambungkan device Anda.');
    }

    public function testOnboardingNavbarStep6() {
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
           ->visit('/')
           ->see('Jika masa berlaku Free Trial sudah habis dan Anda tertarik berlangganan, silakan klik laman pembayaran.');
    }

    public function testOnboardingNavbarChooseCompanyVisible() {
      $objPerusahaan = array (
        0 =>
        (object) array(
           'id' => 10958,
           'nama' => 'PT TEST 1',
           'status' => 'a',
           'kode' => '6468',
        )
      );
      $user = factory(App\User::class)->make([
        'email' => 'dewa@gmail.com',
      ]);
      $this->actingAs($user)
           ->withSession([
             'iduser_perusahaan' => '4',
             'conf_bahasaperusahaan' => 'id',
             'conf_perusahaan' => $objPerusahaan,
           ])
           ->visit('/')
           ->see('Klik untuk mengaktifkan perusahaan')
           ->see('$(\'[data-toggle="popover_pilihperusahaan"]\').popover(\'show\')');
    }

    public function testOnboardingNavbarChooseCompanyNotVisible() {
      $user = factory(App\User::class)->make([
        'email' => 'dewa@gmail.com',
      ]);
      $this->actingAs($user)
           ->withSession([
             'conf_bahasaperusahaan' => 'id'
           ])
           ->visit('/')
           ->see('Klik untuk mengaktifkan perusahaan')
           ->dontSee('$(\'[data-toggle="popover_pilihperusahaan"]\').popover(\'show\')');
    }

    public function testClickAttribut() {
      $this->visit('/')
           ->see('login');

      $this->submitForm('login', [
              'email' => 'dewa@gmail.com',
              'password' => 'tester'
            ]);
      $this->visit('/')->see('Langkah 1: Silakan isi bagian Atribut.');
      $this->click('+ Attribut')
           ->seePageIs('/datainduk/pegawai/atribut/create?onboarding=true')
           ->see('Atribut bisa berupa divisi atau segala variabel yang membentuk kerangka organisasi perusahaan Anda. Contoh: Divisi Marketing, Divisi IT, dsb.');
      $this -> see('Opsional');
      $this->withSession(['onboardingstep' => 1])
           ->submitForm('submit', [
             'atribut' => base64_encode(random_bytes(5)),
             'kode' => base64_encode(random_bytes(5)),
             'tampilpadaringkasan' => 'y',
             'penting' => 'y',
             'jumlahinputan' => 'satu'
           ]);

      $this->seePageIs('/datainduk/pegawai/atribut?onboarding=true');
      $this->see('Langkah 2: Silakan isi jam kerja yang berlaku di perusahaan Anda.')->see('Selesai! Atribut telah disimpan.');
    }

    public function testClickAttributNonOnboarding() {
      $this->visit('/')
           ->see('login');

      $this->submitForm('login', [
              'email' => 'dewa@gmail.com',
              'password' => 'tester'
            ]);

      $this->withSession(['onboardingstep' => 1])
           ->visit('/datainduk/pegawai/atribut/create')
           ->see('Langkah 1: Silakan isi bagian Atribut.');

      $this->withSession(['onboardingstep' => 1])
           ->submitForm('submit', [
             'atribut' => base64_encode(random_bytes(5)),
             'kode' => base64_encode(random_bytes(5)),
             'tampilpadaringkasan' => 'y',
             'penting' => 'y',
             'jumlahinputan' => 'satu'
           ]);

      $this->seePageIs('/datainduk/pegawai/atribut');
      $this->see('Langkah 2: Silakan isi jam kerja yang berlaku di perusahaan Anda.');
    }
}
