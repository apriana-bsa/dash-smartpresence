<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Canopus;
use Mock\CanopusMock;

class CanopusTest extends TestCase
{
    use DatabaseTransactions;

    public function testSignatureGenerator() {
        $fields = array(
            "secret" => env('CANOPUS_SECRET')
        );
        $signature = Canopus::generateSignature($fields);
        $this -> assertNotFalse($signature);
    }

    public function testSignatureValidator() {
        $fields = CanopusMock::canopusMock('notifikasi');
        $fields = json_encode($fields['bodyResponse']);
        $fields = json_decode($fields);
        $result = Canopus::validateSignature($fields);
        $this -> assertEquals(1,$result);
    }

    public function testSignatureValidatorFail() {
      $fields = CanopusMock::canopusMock('notifikasi');
      $fields = json_encode($fields['bodyResponse']);
      $fields = json_decode($fields, TRUE);
      $fields['signature'] = '12345';
      $fields = json_encode($fields);
      $fields = json_decode($fields);
      $result = Canopus::validateSignature($fields);
      $this -> assertEquals(0,$result);
  }

    public function testTokenGenerator() {
        $this -> withSession([
            'isUnitTest' => 'token'
        ]);
        $expected = "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJqdGkiOiIzNGczMXlhMGt4eXN1eDZlIiwiaWF0IjoxNTk2NTMxOTE1fQ.O4ZBW956n4pmcy6QOu6ScEvA5Jyo9uajzPSyIOEqq84MXe-QvXZbSG5kswMDZ7ZJnyxpZSJXCw0D7fceWbhA1aJ3rRvka7SH47kyeMLeouAM_SZ67I5QEPBr5Flsle0ujw3o5w-_RbT94mSWTnhFJD-NZ292wFbF_jp0-ZM3RFyn5ctH-3eubn_mWVxoV5L14Y2BsS6WcDubY8remLll9YI6ZHWLfYO6fR4O0HZjJclA5wxdc2Z_QP8SJgUJxe7kXVPf8jgGMbEG5S-zXhlklg41Bw9sN8aYeXo-V3GvX7CsqpxKW0SLFShrclHXObqqLttRpCwED8Rch4WwErj2Cw";
        $token = Canopus::generateToken();
        $this -> assertEquals($expected,$token);
    }

    public function testCartGenerator() {
      $this -> withSession([
          'isUnitTest' => 'cart'
      ]);
      $data = array (
        'orderId' => '12345',
        'amount' => 10000,
        'periode' => '3',
        'customerDetails' => array (
            'firstName' => 'Tes Create Cart',
            'email' => 'createcart@gmail.com',
            'phone' => '08123456789'
          )
      );
      $expected = "https://canopus-auth.sumpahpalapa.com/transaction/payment/9e26144406f36e7a6adc74d2";
      $_SERVER['HTTP_HOST'] = 'localhost';
      $response = Canopus::createCart($data);
      $checkoutUrl = $response['checkoutUrl'];
      $this -> assertEquals($expected,$checkoutUrl);
  }

  public function testNotifikasiSuccess() {
      factory(App\Invoice::class)->create([
        'order_id' => 'SPA10950-25',
        'idperusahaan' => 10950,
      ]);
      $request = CanopusMock::canopusMock('notifikasi');
      $request = json_encode($request['bodyResponse']);
      $request = json_decode($request, TRUE);
      $response = $this->json('POST', 'canopus/notifikasi', $request);
      $expected = "Callback Received, Payment Updated to Success, pdf and email success send";
      $response->see($expected);
  }

  public function testNotifikasiAlreadyUpdated() {
    factory(App\Invoice::class)->create([
      'order_id' => 'SPA10950-25',
      'idperusahaan' => 10950,
      'status_bayar' => 2,
    ]);
    $request = CanopusMock::canopusMock('notifikasi');
    $request = json_encode($request['bodyResponse']);
    $request = json_decode($request, TRUE);
    $response = $this->json('POST', 'canopus/notifikasi', $request);
    $expected = "Callback Received, Payment Already Updated";
    $response->see($expected);
  }

  public function testNotifikasiInvalidSignature() {
    factory(App\Invoice::class)->create([
      'order_id' => 'SPA10950-25',
      'idperusahaan' => 10950,
    ]);
    $request = CanopusMock::canopusMock('notifikasi');
    $request = json_encode($request['bodyResponse']);
    $request = json_decode($request, TRUE);
    $request['signature'] = '12345';
    $response = $this->json('POST', 'canopus/notifikasi', $request);
    $expected = "Invalid Signature";
    $response->see($expected);
  }

  public function testNotifikasiPending() {
    factory(App\Invoice::class)->create([
      'order_id' => 'SPA10950-27',
      'idperusahaan' => 10950,
    ]);
    $request = CanopusMock::canopusMock('notifikasiPending');
    $request = json_encode($request['bodyResponse']);
    $request = json_decode($request, TRUE);
    $response = $this->json('POST', 'canopus/notifikasi', $request);
    $expected = "Callback Received, Payment Updated to Pending";
    $response->see($expected);
  }

  public function testNotifikasiExpired() {
    factory(App\Invoice::class)->create([
      'order_id' => 'SP-20200211144917',
      'idperusahaan' => 10950,
    ]);
    $request = CanopusMock::canopusMock('notifikasiExpired');
    $request = json_encode($request['bodyResponse']);
    $request = json_decode($request, TRUE);
    $response = $this->json('POST', 'canopus/notifikasi', $request);
    $expected = "Callback Received, Payment Updated to Expired";
    $response->see($expected);
  }
}
