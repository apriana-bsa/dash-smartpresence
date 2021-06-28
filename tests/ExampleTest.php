<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
// use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{
    // use DatabaseMigrations;
    use DatabaseTransactions;
    /**
     * A basic functional test example.
     *
     * @return void
     */
    public function testBasicExample()
    {
        $this->visit('/')
             ->see('Login');
    }

    public function testDatabase()
    {
        // Make call to application...
        $this->seeInDatabase('user', ['email' => 'dewa@gmail.com']);
    }

    public function testVisitPembayaran()
    {
        // $this->visit('/pembayaran')
        //      ->see('3 Bulan');
    }

    public function testSubscriptionTrue()
    {

        // $user = factory(User::class)->create([
        //     'password' => bcrypt($password = 'tester'),
        // ]);
        //
        // $response = $this->post('/login', [
        //     'email' => $user->email,
        //     'password' => $password,
        // ]);
        //
        // $response->assertRedirect('/home');
        // $this->assertAuthenticatedAs($user);
        // $this->visit('/')
        //      ->see('Login');
        //
        // $this->submitForm('login', [
        //     'email'    => 'dewa@gmail.com',
        //     'password' => 'tester',
        // ]);
        $user = factory(App\User::class)->make([
          'email' => 'dewa@gmail.com',
        ]);
        $this->actingAs($user)
             ->withSession([
               'conf_bahasaperusahaan' => 'id',
               'perusahaan_expired' => 'Ya',
               'perusahaan_unitprice' => '7000',
               'perusahaan_limitpegawai' => '10',
               'perusahaan_subscription' => '1'
             ])
             ->visit('/pembayaran')
             ->see('3 Bulan');

    }

    public function testSubscriptionFalse()
    {
        $user = factory(App\User::class)->make([
          'email' => 'dewa@gmail.com',
        ]);
        $this->actingAs($user)
             ->withSession([
               'conf_bahasaperusahaan' => 'id',
               'perusahaan_expired' => 'Ya',
               'perusahaan_unitprice' => '7000',
               'perusahaan_limitpegawai' => '10',
               'perusahaan_subscription' => '0'
             ])
             ->visit('/pembayaran')
             ->see('Pembayaran')
             ->dontSee('3 Bulan');

    }

    public function testLoginTrue()
    {
        $credential = [
            'email' => 'dewa@gmail.com',
            'password' => 'tester'
        ];

        $response = $this->call('POST','login',$credential);
        $this->assertEquals(302, $response->status());
        $this->assertRedirectedTo('/');
    }

    public function testLoginFalse()
    {
        $credential = [
            'email' => 'dewa@gmail.com',
            'password' => 'tester2'
        ];

        $response = $this->call('POST','login',$credential);
        $this->assertEquals(302, $response->status());
        $this->assertRedirectedTo('/login');
        //
        // $response->assertSessionMissing('errors');
        // $this->visit('/login')
        //  ->type('dewa@gmail.com', 'email')
        //  ->type('tester', 'password')
        //  ->press('login')
        //  ->seePageIs('/');
        // $response = $this->call('GET', '/pembayaran');
        // $this->assertEquals(200, $response->status());
    }
}
