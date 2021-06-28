<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class PopoverJamPegawaiTest extends TestCase
{
  use DatabaseTransactions;

    public function testPopoverJamPegawai1() {
      $this -> visit("/");
      $this -> submitForm('login', [
          'email' => 'dewa@gmail.com',
          'password' => 'tester'
      ])
   
           ->click('+ Jam Kerja Pegawai')
           ->seePageIs('/datainduk/absensi/jamkerjapegawai?onboarding=true')
           ->see('Silakan klik centang pada semua nama pegawai yang akan ditentukan jam kerjanya.');
    }

    public function testPopoverJamPegawai2() {
        $this -> visit("/");
        $this -> submitForm('login', [
            'email' => 'dewa@gmail.com',
            'password' => 'tester'
        ])
     
             ->click('+ Jam Kerja Pegawai')
             ->seePageIs('/datainduk/absensi/jamkerjapegawai?onboarding=true')
             ->see('Silakan klik untuk tentukan periode dan jam kerja.');
      }
    
    public function testPopoverJamPegawai3() {
        $this -> visit("/");
        $this -> submitForm('login', [
            'email' => 'dewa@gmail.com',
            'password' => 'tester'
        ])
     
             ->click('+ Jam Kerja Pegawai')
             ->seePageIs('/datainduk/absensi/jamkerjapegawai?onboarding=true')
             ->see('Silakan tentukan jam kerja pegawai dan periode efektif berlakunya.');
    }

    public function testPopoverJamPegawai4() {
        $this -> visit("/");
        $this -> submitForm('login', [
            'email' => 'dewa@gmail.com',
            'password' => 'tester'
        ])
     
             ->click('+ Jam Kerja Pegawai')
             ->seePageIs('/datainduk/absensi/jamkerjapegawai?onboarding=true')
             ->see('Selamat! Jam kerja telah ditentukan.');
    }

    public function testPopoverJamPegawai5() {
        $this -> visit("/");
        $this -> submitForm('login', [
            'email' => 'dewa@gmail.com',
            'password' => 'tester'
        ])
     
             ->click('+ Jam Kerja Pegawai')
             ->seePageIs('/datainduk/absensi/jamkerjapegawai?onboarding=true')
             ->see('Klik tombol ini untuk memasukkan pegawai pada jam kerja yang telah ditentukan.');
    }

    public function testPopoverJamPegawai6() {
        $this -> visit("/");
        $this -> submitForm('login', [
            'email' => 'dewa@gmail.com',
            'password' => 'tester'
        ])
     
             ->click('+ Jam Kerja Pegawai')
             ->seePageIs('/datainduk/absensi/jamkerjapegawai?onboarding=true')
             ->see('Jam kerja pegawai telah berhasil ditambahkan.');
    }

    public function testPopoverJamPegawai7() {
        $this -> visit("/");
        $this -> submitForm('login', [
            'email' => 'dewa@gmail.com',
            'password' => 'tester'
        ])
     
             ->click('+ Jam Kerja Pegawai')
             ->seePageIs('/datainduk/absensi/jamkerjapegawai?onboarding=true')
             ->see('Anda dapat memfilter data pegawai berdasarkan atribut dan jam kerjanya');
      }
}
