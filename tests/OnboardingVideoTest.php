<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class OnboardingVideoTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testVideoPopUpVisible()
    {

      $user = factory(App\User::class)->make([
        'email' => 'dewa@gmail.com',
      ]);
      $this->actingAs($user)
           ->withSession([
             'conf_bahasaperusahaan' => 'id',
             'conf_webperusahaan' => '10950',
             'iduser_perusahaan' => '4',
             'onboardingstep' => '1',
             'onboardingvideo' => '1',
             'enable_onboarding' => '1'
           ])
           ->visit('/')
           ->see('$(\'#modalvideo\').modal(\'show\');');
    }

    public function testVideoPopUpNotVisible()
    {
      $user = factory(App\User::class)->make([
        'email' => 'dewa@gmail.com',
      ]);
      $this->actingAs($user)
           ->withSession([
             'conf_bahasaperusahaan' => 'id',
             'iduser_perusahaan' => '4',
             'onboardingstep' => '1',
             'onboardingvideo' => '0',
             'enable_onboarding' => '1'
           ])
           ->visit('/')
           ->dontSee('$(\'#modalvideo\').modal(\'show\');');
    }
}
