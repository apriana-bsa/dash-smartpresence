<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class OnboardingSambungkanDeviceTest extends TestCase
{
  use DatabaseTransactions;

    public function testSambungDevice1() {
      $this -> visit("/");
      $this -> submitForm('login', [
          'email' => 'dewa@gmail.com',
          'password' => 'tester'
      ])
            ->withSession([
                'conf_bahasaperusahaan' => 'id',
                'onboardingstep' => '5'
            ])
           ->click('+ Sambungkan ke Device')
           ->seePageIs('/datainduk/absensi/mesin/create?onboarding=true')
           ->see('Diisi dengan nama perangkat yang akan disambungkan. Contoh: Samsung Galaxy S10 atau Finger Print Kantor Denpasar, dsb.');
    }

    public function testSambungDevice2() {
        $this -> visit("/");
        $this -> submitForm('login', [
            'email' => 'dewa@gmail.com',
            'password' => 'tester'
        ])
              ->withSession([
                  'conf_bahasaperusahaan' => 'id',
                  'onboardingstep' => '5'
              ])
             ->click('+ Sambungkan ke Device')
             ->seePageIs('/datainduk/absensi/mesin/create?onboarding=true')
             ->see('Diisi sesuai dengan jenis perangkat yang digunakan untuk absensi.');
    }

    public function testSambungDevice3() {
        $this -> visit("/");
        $this -> submitForm('login', [
            'email' => 'dewa@gmail.com',
            'password' => 'tester'
        ])
              ->withSession([
                  'conf_bahasaperusahaan' => 'id',
                  'onboardingstep' => '5'
              ])
             ->click('+ Sambungkan ke Device')
             ->seePageIs('/datainduk/absensi/mesin/create?onboarding=true')
             ->see('Klik "Ya" untuk menyesuaikan jam server dengan jam perangkat Anda.');
    }

    public function testSambungDevice4() {
        $this -> visit("/");
        $this -> submitForm('login', [
            'email' => 'dewa@gmail.com',
            'password' => 'tester'
        ])
              ->withSession([
                  'conf_bahasaperusahaan' => 'id',
                  'onboardingstep' => '5'
              ])
             ->click('+ Sambungkan ke Device')
             ->seePageIs('/datainduk/absensi/mesin/create?onboarding=true')
             ->see('Klik "Ya" untuk menyesuaikan zona waktu dengan lokasi perangkat.');
    }

    public function testSambungDevice5() {
        $this -> visit("/");
        $this -> submitForm('login', [
            'email' => 'dewa@gmail.com',
            'password' => 'tester'
        ])
              ->withSession([
                  'conf_bahasaperusahaan' => 'id',
                  'onboardingstep' => '5'
              ])
             ->click('+ Sambungkan ke Device')
             ->seePageIs('/datainduk/absensi/mesin/create?onboarding=true')
             ->see('Pilih lokasi di peta untuk mengaktifkan GPS restriction');
    }

    public function testSambungDevice6() {
        $this -> visit("/");
        $this -> submitForm('login', [
            'email' => 'dewa@gmail.com',
            'password' => 'tester'
        ])
              ->withSession([
                  'conf_bahasaperusahaan' => 'id',
                  'onboardingstep' => '5'
              ])
             ->click('+ Sambungkan ke Device')
             ->seePageIs('/datainduk/absensi/mesin/create?onboarding=true')
             ->see('Pilih atribut pegawai yang dapat menggunakan atau melakukan absensi di perangkat ini.');
    }

    public function testSambungDevice7() {
        $this -> visit("/");
        $this -> submitForm('login', [
            'email' => 'dewa@gmail.com',
            'password' => 'tester'
        ])
              ->withSession([
                  'conf_bahasaperusahaan' => 'id',
                  'onboardingstep' => '5'
              ])
             ->visit('/datainduk/absensi/mesin?onboarding=true')
             ->see('Klik di sini untuk menyambungkan smartphone pegawai anda.');
    }

    public function testSambungDevice8() {
        $this -> visit("/");
        $this -> submitForm('login', [
            'email' => 'dewa@gmail.com',
            'password' => 'tester'
        ])
              ->withSession([
                  'conf_bahasaperusahaan' => 'id',
                  'onboardingstep' => '5'
              ])
             ->visit('/datainduk/absensi/mesin?onboarding=true')
             ->see('Perangkat berhasil ditambahkan.');
    }

    public function testSambungDevice9() {
        $this -> visit("/");
        $this -> submitForm('login', [
            'email' => 'dewa@gmail.com',
            'password' => 'tester'
        ])
              ->withSession([
                  'conf_bahasaperusahaan' => 'id',
                  'onboardingstep' => '5'
              ])
             ->visit('/datainduk/absensi/mesin?onboarding=true')
             ->see('Kode verifikasi berhasil ditambahkan. Pegawai dapat menggunakan kode ini untuk melakukan absensi di aplikasi SmartPresence dCap.'); 
    }

    public function testSambungDevice10() {
        $this -> visit("/");
        $this -> submitForm('login', [
            'email' => 'dewa@gmail.com',
            'password' => 'tester'
        ])
              ->withSession([
                  'conf_bahasaperusahaan' => 'id',
                  'onboardingstep' => '5'
              ])
             ->visit('/datainduk/absensi/mesin/487/verifikasi?onboarding=true')
             ->see('Masukkan Device ID pegawai. Device ID didapatkan dari laman pertama aplikasi SmartPresence dCap. Download aplikasinya di Google Play atau App Store.');
    }
}
