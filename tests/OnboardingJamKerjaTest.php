<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OnboardingJamKerjaTest extends TestCase
{
    use DatabaseTransactions;

    //full scenario
    public function testClickJamKerja() {
        $this->visit('/')
             ->see('Login');
        $this->submitForm('login', [
            'email' => 'testing@gmail.com',
            'password' => 'tester',
        ]);

        $this->click('+ Jam Kerja')
             ->seePageIs('/datainduk/absensi/jamkerja/create?onboarding=true')
             ->see('$(\'[data-toggle="popover-jamkerjaform"]\').popover(\'show\');');

        $nama = 'UnitTest ' . base64_encode(random_bytes(5));
        $this->submitForm('submit', [
            'nama' => $nama,
            'jenis' => 'full',
            'digunakan' => 'y'
        ]);

        $this->seePageIs('/datainduk/absensi/jamkerja?onboarding=true')
             ->see('$(\'[data-toggle="popover-jamkerja-bt-detail"]\').popover(\'show\');');

        $this->visit('/datainduk/absensi/jamkerja/129/full?onboarding=true')
             ->seePageIs('/datainduk/absensi/jamkerja/129/full?onboarding=true')
             ->see('$(\'[data-toggle="popover-botton-add-data"]\').popover(\'show\')')
             ->see('$(\'[data-toggle="popover-jamkerja-detail"]\').popover(\'hide\')');

        $this->visit('/datainduk/absensi/jamkerja/129/full/create?onboarding=true')
             ->seePageIs('/datainduk/absensi/jamkerja/129/full/create?onboarding=true')
             ->see('$(\'[data-toggle="popover-checkbox-fulltime-jamkerja"]\').popover(\'show\')');

        $this->submitForm('submit', [
            'full_masukkerjasenin' => 'y',
            'full_jammasuksenin' => '09:00',
            'full_jampulangsenin' => '18:00'
        ]);

        $this->seePageIs('/datainduk/absensi/jamkerja/129/full?onboarding=true')
             ->see('$(\'[data-toggle="popover-botton-add-data"]\').popover(\'hide\')')
             ->see('$(\'[data-toggle="popover-jamkerja-detail"]\').popover(\'show\')');

    }

    //flow 1
    public function testJamKerjaNonOnboarding() {
        $this->visit('/')
             ->see('Login');
        $this->submitForm('login', [
            'email' => 'testing@gmail.com',
            'password' => 'tester',
        ]);

        $this->visit('/datainduk/absensi/jamkerja')
             ->seePageIs('/datainduk/absensi/jamkerja')
             ->dontSee('$(\'[data-toggle="popover-jamkerjaform"]\').popover(\'show\');');
    }

    //flow 2
    public function testJamKerjaDetailNonOnboarding() {
        $this->visit('/')
             ->see('Login');
        $this->submitForm('login', [
            'email' => 'testing@gmail.com',
            'password' => 'tester',
        ]);

        $this->visit('/datainduk/absensi/jamkerja/80/full')
             ->seePageIs('/datainduk/absensi/jamkerja/80/full')
             ->dontSee('$(\'[data-toggle="popover-botton-add-data"]\').popover(\'hide\')')
             ->dontSee('$(\'[data-toggle="popover-jamkerja-detail"]\').popover(\'show\')');
    }

    //flow 3
    public function testJamKerjaDetailCreateNonOnboarding() {
        $this->visit('/')
             ->see('Login');
        $this->submitForm('login', [
            'email' => 'testing@gmail.com',
            'password' => 'tester',
        ]);

        $this->visit('/datainduk/absensi/jamkerja/80/full/create')
             ->seePageIs('/datainduk/absensi/jamkerja/80/full/create')
             ->dontSee('$(\'[data-toggle="popover-checkbox-fulltime-jamkerja"]\').popover(\'show\')');
    }
}
