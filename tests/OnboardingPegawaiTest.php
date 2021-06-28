<?php

// use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OnboardingPegawaiTest extends TestCase
{
    use DatabaseTransactions;

    // Setting this allows both DB connections to be reset between tests
//     protected $connectionsToTransact = ['perusahaan_db'];
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }

    public function testClickPegawai() {
        $this->visit('/')
             ->see('login');

        $user = $this->submitForm('login', [
            'email' => 'testing@gmail.com',
            'password' => 'tester'
        ]);

        $this->click('+ Pegawai')
             ->seePageIs('/datainduk/pegawai/pegawai/create?onboarding=true')
             ->see('$(\'[data-toggle="popover-pegawai-pin"]\').popover(\'show\');')
             ->see('$(\'[data-toggle="popover-pegawai-simpan"]\').popover(\'show\');');

        $this->submitForm('submit', [
            'nama' => 'UnitTest ' . base64_encode(random_bytes(5)),
            'tanggalaktif' => '19/08/2020',
            'status' => 'a'
        ]);

        $this->seePageIs('/datainduk/pegawai/pegawai?onboarding=true')
             ->see('$(\'[data-toggle="popover-pegawai-list"]\').popover(\'show\');');
    }

    public function testPegawaiNonOnboarding() {
        $this->visit('/')
             ->see('login');

        $this->submitForm('login', [
            'email' => 'testing@gmail.com',
            'password' => 'tester'
        ]);

        $this->visit('/datainduk/pegawai/pegawai')
             ->seePageIs('/datainduk/pegawai/pegawai')
             ->dontSee('$(\'[data-toggle="popover-pegawai-list"]\').popover(\'show\');');

        $this->visit('/datainduk/pegawai/pegawai/create')
             ->seePageIs('/datainduk/pegawai/pegawai/create')
             ->dontSee('$(\'[data-toggle="popover-pegawai-pin"]\').popover(\'show\');')
             ->dontSee('$(\'[data-toggle="popover-pegawai-simpan"]\').popover(\'show\');');
    }
}
