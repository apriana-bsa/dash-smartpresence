<div class="row">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-content text-center p-md">
              @if(Session::get('perusahaan_expired') == 'tidak')
                <h2>
                    {{ trans('all.pembayaran') }}
                </h2>
              @else
                <h2>
                    {{ trans('all.perusahaantelahexpired') }}
                </h2>
              @endif
              <p>{{ trans('all.silakan_lakukan_pembayaran') }}</p><br/>

              @if(Session::get('onboardingstep')==6 && Session::get('perusahaan_subscription') && url()->current() == url('/pembayaran'))
                <!-- START Modal Selamat -->
                  <div class="modal fade" id="modal-congrat-sambungkan-device-done">
                    <div class="modal-dialog modal-dialog-centered">
                      <div class="modal-content">

                        <!-- Modal body -->
                        <div class="modal-body">
                        <a class="close" data-dismiss="modal">&times;</a>
                          <center>
                          <h2><b style='font-size: 36px;'>{{ trans('onboarding.mesin_congrat_title') }}</b></h2><br/>

                          <div><img src="{{ url('lib/img/fireworks-grey.png') }}" height="70px"></div><br/>
                          <p style='font-size: 18px;'>{{ trans('onboarding.mesin_congrat_anda_baru_saja') }}</p> <br/>
                          <p style='font-size: 14px;'>{{ trans('onboarding.mesin_congrat_dengan_menyelesaikan') }}</p><br/>
                          <p><b style='font-size: 16px;'>{{ trans('onboarding.mesin_congrat_anda_tetap') }}</b></p>
                          </center>
                        </div>

                      </div>
                    </div>
                  </div>
                <!-- END Modal Selamat -->
                <script>
                  $(document).ready(function(){
                      //hide tooltip "Langkah 6"
                      $('[data-toggle="popover-payment"]').popover('hide');
                      //show congrat popup
                      $('#modal-congrat-sambungkan-device-done').modal('show')
                      .on("hidden.bs.modal", function () {
                          //show tooltip "Langkah 6" when modal dismiss
                          $('[data-toggle="popover-payment"]').popover('show');
                          {{ App\Http\Controllers\Controller::incrementOnboardingstep(6) }}
                      });
                  });
                </script>
              @endif

              @if(Session::get('perusahaan_subscription'))
              <form id="form-pembayaran" role="form" action="{{ url('/pembayaran') }}" method="post">
                {{ csrf_field() }}
                <div class="form-group">
                  <div class="row">
                    <div class="col-lg-12">
                      <div class="col-lg-3">
                        <div class="card">
                          <div class="card-body">
                            <h1 class="card-title">{{ trans('all.1_bulan') }}</h1>
                            <p class="card-text">
                              <b>{{ trans('all.benefits') }}:</b>
                              <table>
                                <tr>
                                  <td style="padding: 4px; text-align: left;">
                                    <span class="benefits"><i class="glyphicon glyphicon-check text-success"></i> {{ trans('all.unlimit_install_absensi') }}</span>
                                  <td>
                                </tr>
                                <tr>
                                  <td style="padding: 4px; text-align: left;">
                                    <span class="benefits"><i class="glyphicon glyphicon-check text-success"></i> {{ trans('all.unlimit_install_dasbor') }}</span>
                                  <td>
                                </tr>
                                <tr>
                                  <td style="padding: 4px; text-align: left;">
                                    <span class="benefits"><i class="glyphicon glyphicon-check text-success"></i> {{ trans('all.unlimit_install_karyawan') }}</span>
                                  <td>
                                </tr>
                                <tr>
                                  <td style="padding: 4px; text-align: left;">
                                    <span class="benefits"><i class="glyphicon glyphicon-check text-success"></i> {{ trans('all.support_chat_phone') }}</span>
                                  <td>
                                </tr>
                                <tr>
                                  <td style="padding: 4px; text-align: left;">
                                    <span class="benefits"><i class="glyphicon glyphicon-check text-success"></i> {{ trans('all.support_setup_sistem') }}</span>
                                  <td>
                                </tr>
                              </table>
                              <br/>
                              <table class="table table-responsive">
                                <tbody>
                                  <tr>
                                    <td style="text-align:left">{{ trans('all.harga_unit') }}</td>
                                    <td style="text-align:right">
                                      <b>Rp{{ number_format(Session::get('perusahaan_unitprice'), 0, ",", ".")}}</b>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td style="text-align:left">{{ trans('all.jumlah_user') }}</td>
                                  @if (Session::get('enable_onboarding'))
                                    <td style="text-align:right"><input type="number" min="1" style="text-align: right" id="kuota_1bulan" name="kuota_1bulan" class="form-control" placeholder="User Kuota" aria-hidden="true" value="{{ Session::get('perusahaan_limitpegawai')}}" onchange="changeKuota(1,this.value)"></td>
                                  @else
                                    <td style="text-align:right"><b>{{ Session::get('perusahaan_limitpegawai')}} </b></td>
                                  @endif
                                  </tr>
                                  <tr>
                                    <td style="text-align:left">{{ trans('all.harga_subtotal') }}</td>
                                    <td style="text-align:right"><b id="subtotal_1bulan">Rp{{ number_format(Session::get('perusahaan_unitprice')*Session::get('perusahaan_limitpegawai'), 0, ",", ".")}}</b></td>
                                  </tr>
                                  <tr>
                                    <td style="text-align:left">{{ trans('all.ppn10') }}</td>
                                    <td style="text-align:right"><b id="ppn_1bulan">Rp{{ number_format(Session::get('perusahaan_unitprice')*Session::get('perusahaan_limitpegawai')*0.1, 0, ",", ".")}}</b></td>
                                  </tr>
                                  <tr style="font-size:16px">
                                    <td style="text-align:left">{{ trans('all.harga_total') }}</td>
                                    <td style="text-align:right"><b id="total_1bulan">Rp{{ number_format(Session::get('perusahaan_unitprice')*Session::get('perusahaan_limitpegawai')*1.1, 0, ",", ".")}}</b></td>
                                  </tr>
                                </tbody>
                              </table>
                            </p>
                            <button type="submit" aria-hidden="true" name="periode" value="1" class="btn btn-primary">{{ trans('all.bayar')." ".trans('all.1_bulan') }}</button>
                          </div>
                        </div>
                      </div>
                      <div class="col-lg-3">
                        <div class="card">
                          <div class="card-body">
                            <h1 class="card-title">{{ trans('all.3_bulan') }}</h1>
                            <p class="card-text">
                              <b>{{ trans('all.benefits') }}:</b>
                              <table>
                                <tr>
                                  <td style="padding: 4px; text-align: left;">
                                    <span class="benefits"><i class="glyphicon glyphicon-check text-success"></i> {{ trans('all.unlimit_install_absensi') }}</span>
                                  <td>
                                </tr>
                                <tr>
                                  <td style="padding: 4px; text-align: left;">
                                    <span class="benefits"><i class="glyphicon glyphicon-check text-success"></i> {{ trans('all.unlimit_install_dasbor') }}</span>
                                  <td>
                                </tr>
                                <tr>
                                  <td style="padding: 4px; text-align: left;">
                                    <span class="benefits"><i class="glyphicon glyphicon-check text-success"></i> {{ trans('all.unlimit_install_karyawan') }}</span>
                                  <td>
                                </tr>
                                <tr>
                                  <td style="padding: 4px; text-align: left;">
                                    <span class="benefits"><i class="glyphicon glyphicon-check text-success"></i> {{ trans('all.support_chat_phone') }}</span>
                                  <td>
                                </tr>
                                <tr>
                                  <td style="padding: 4px; text-align: left;">
                                    <span class="benefits"><i class="glyphicon glyphicon-check text-success"></i> {{ trans('all.support_setup_sistem') }}</span>
                                  <td>
                                </tr>
                              </table>
                              <br/>
                              <table class="table table-responsive">
                                <tbody>
                                  <tr>
                                    <td style="text-align:left">{{ trans('all.harga_unit') }}</td>
                                    <td style="text-align:right">
                                      <b>Rp{{ number_format(Session::get('perusahaan_unitprice'), 0, ",", ".")}}</b>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td style="text-align:left">{{ trans('all.jumlah_user') }}</td>
                                    @if (Session::get('enable_onboarding'))
                                      <td style="text-align:right"><input type="number" min="1" style="text-align: right"  id="kuota_3bulan" name="kuota_3bulan" class="form-control" placeholder="User Kuota" aria-describedby="sizing-addon1" value="{{ Session::get('perusahaan_limitpegawai')}}" onchange="changeKuota(3,this.value)"></td>
                                    @else
                                      <td style="text-align:right"><b>{{ Session::get('perusahaan_limitpegawai')}} </b></td>
                                    @endif
                                  </tr>
                                  <tr>
                                    <td style="text-align:left">{{ trans('all.harga_subtotal') }}</td>
                                    <td style="text-align:right"><b id="subtotal_3bulan">Rp{{ number_format(Session::get('perusahaan_unitprice')*Session::get('perusahaan_limitpegawai')*3, 0, ",", ".")}}</b></td>
                                  </tr>
                                  <tr>
                                    <td style="text-align:left">{{ trans('all.ppn10') }}</td>
                                    <td style="text-align:right"><b id="ppn_3bulan">Rp{{ number_format(Session::get('perusahaan_unitprice')*Session::get('perusahaan_limitpegawai')*3*0.1, 0, ",", ".")}}</b></td>
                                  </tr>
                                  <tr style="font-size:16px">
                                    <td style="text-align:left">{{ trans('all.harga_total') }}</td>
                                    <td style="text-align:right"><b id="total_3bulan">Rp{{ number_format(Session::get('perusahaan_unitprice')*Session::get('perusahaan_limitpegawai')*3*1.1, 0, ",", ".")}}</b></td>
                                  </tr>
                                </tbody>
                              </table>
                            </p>
                            <button type="submit" aria-hidden="true" name="periode" value="3" class="btn btn-primary">{{ trans('all.bayar')." ".trans('all.3_bulan') }}</button>
                          </div>
                        </div>
                      </div>
                      <div class="col-lg-3">
                        <div class="card">
                          <div class="card-body">
                            <h1 class="card-title">{{ trans('all.6_bulan') }}</h1>
                            <p class="card-text">
                              <b>{{ trans('all.benefits') }}:</b>
                              <table>
                                <tr>
                                  <td style="padding: 4px; text-align: left;">
                                    <span class="benefits"><i class="glyphicon glyphicon-check text-success"></i> {{ trans('all.unlimit_install_absensi') }}</span>
                                  <td>
                                </tr>
                                <tr>
                                  <td style="padding: 4px; text-align: left;">
                                    <span class="benefits"><i class="glyphicon glyphicon-check text-success"></i> {{ trans('all.unlimit_install_dasbor') }}</span>
                                  <td>
                                </tr>
                                <tr>
                                  <td style="padding: 4px; text-align: left;">
                                    <span class="benefits"><i class="glyphicon glyphicon-check text-success"></i> {{ trans('all.unlimit_install_karyawan') }}</span>
                                  <td>
                                </tr>
                                <tr>
                                  <td style="padding: 4px; text-align: left;">
                                    <span class="benefits"><i class="glyphicon glyphicon-check text-success"></i> {{ trans('all.support_chat_phone') }}</span>
                                  <td>
                                </tr>
                                <tr>
                                  <td style="padding: 4px; text-align: left;">
                                    <span class="benefits"><i class="glyphicon glyphicon-check text-success"></i> {{ trans('all.support_setup_sistem') }}</span>
                                  <td>
                                </tr>
                              </table>
                              <br/>
                              <table class="table table-responsive">
                                <tbody>
                                  <tr>
                                    <td style="text-align:left">{{ trans('all.harga_unit') }}</td>
                                    <td style="text-align:right">
                                      <b>Rp{{ number_format(Session::get('perusahaan_unitprice'), 0, ",", ".")}}</b>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td style="text-align:left">{{ trans('all.jumlah_user') }}</td>
                                    @if (Session::get('enable_onboarding'))
                                      <td style="text-align:right"><input type="number" min="1" style="text-align: right"  id="kuota_6bulan" name="kuota_6bulan" class="form-control" placeholder="User Kuota" aria-describedby="sizing-addon1" value="{{ Session::get('perusahaan_limitpegawai')}}" onchange="changeKuota(6,this.value)"></td>
                                    @else
                                      <td style="text-align:right"><b>{{ Session::get('perusahaan_limitpegawai')}} </b></td>
                                    @endif
                                  </tr>
                                  <tr>
                                    <td style="text-align:left">{{ trans('all.harga_subtotal') }}</td>
                                    <td style="text-align:right"><b id="subtotal_6bulan">Rp{{ number_format(Session::get('perusahaan_unitprice')*Session::get('perusahaan_limitpegawai')*6, 0, ",", ".")}}</b></td>
                                  </tr>
                                  <tr>
                                    <td style="text-align:left">{{ trans('all.ppn10') }}</td>
                                    <td style="text-align:right"><b id="ppn_6bulan">Rp{{ number_format(Session::get('perusahaan_unitprice')*Session::get('perusahaan_limitpegawai')*6*0.1, 0, ",", ".")}}</b></td>
                                  </tr>
                                  <tr style="font-size:16px">
                                    <td style="text-align:left">{{ trans('all.harga_total') }}</td>
                                    <td style="text-align:right"><b id="total_6bulan">Rp{{ number_format(Session::get('perusahaan_unitprice')*Session::get('perusahaan_limitpegawai')*6*1.1, 0, ",", ".")}}</b></td>
                                  </tr>
                                </tbody>
                              </table>
                            </p>
                            <button type="submit" aria-hidden="true" name="periode" value="6" class="btn btn-primary">{{ trans('all.bayar')." ".trans('all.6_bulan') }}</button>
                          </div>
                        </div>
                      </div>
                      <div class="col-lg-3">
                        <div class="card">
                          <div class="card-body">
                            <h1 class="card-title">{{ trans('all.1_tahun') }}</h1>
                            <p class="card-text">
                              <b>{{ trans('all.benefits') }}:</b>
                              <table>
                                <tr>
                                  <td style="padding: 4px; text-align: left;">
                                    <span class="benefits"><i class="glyphicon glyphicon-check text-success"></i> {{ trans('all.unlimit_install_absensi') }}</span>
                                  <td>
                                </tr>
                                <tr>
                                  <td style="padding: 4px; text-align: left;">
                                    <span class="benefits"><i class="glyphicon glyphicon-check text-success"></i> {{ trans('all.unlimit_install_dasbor') }}</span>
                                  <td>
                                </tr>
                                <tr>
                                  <td style="padding: 4px; text-align: left;">
                                    <span class="benefits"><i class="glyphicon glyphicon-check text-success"></i> {{ trans('all.unlimit_install_karyawan') }}</span>
                                  <td>
                                </tr>
                                <tr>
                                  <td style="padding: 4px; text-align: left;">
                                    <span class="benefits"><i class="glyphicon glyphicon-check text-success"></i> {{ trans('all.support_chat_phone') }}</span>
                                  <td>
                                </tr>
                                <tr>
                                  <td style="padding: 4px; text-align: left;">
                                    <span class="benefits"><i class="glyphicon glyphicon-check text-success"></i> {{ trans('all.support_setup_sistem') }}</span>
                                  <td>
                                </tr>
                              </table>
                              <br/>
                              <table class="table table-responsive">
                                <tbody>
                                  <tr>
                                    <td style="text-align:left">{{ trans('all.harga_unit') }}</td>
                                    <td style="text-align:right">
                                      <b>Rp{{ number_format(Session::get('perusahaan_unitprice')-env('YEARLY_DISCOUNT',0), 0, ",", ".")}} @if(env('YEARLY_DISCOUNT',0)>0)<s style="font-size:11px">Rp{{number_format(Session::get('perusahaan_unitprice'), 0, ",", ".")}}</s>@endif</b>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td style="text-align:left">{{ trans('all.jumlah_user') }}</td>
                                    @if (Session::get('enable_onboarding'))
                                      <td style="text-align:right"><input type="number" min="1" style="text-align: right"  id="kuota_12bulan" name="kuota_12bulan" class="form-control" placeholder="User Kuota" aria-describedby="sizing-addon1" value="{{ Session::get('perusahaan_limitpegawai')}}" onchange="changeKuota(12,this.value)"></td>
                                    @else
                                      <td style="text-align:right"><b>{{ Session::get('perusahaan_limitpegawai')}} </b></td>
                                    @endif
                                  </tr>
                                  <tr>
                                    <td style="text-align:left">{{ trans('all.harga_subtotal') }}</td>
                                    <td style="text-align:right"><b id="subtotal_12bulan">Rp{{ number_format((Session::get('perusahaan_unitprice')-env('YEARLY_DISCOUNT',0))*Session::get('perusahaan_limitpegawai')*12, 0, ",", ".")}}</b></td>
                                  </tr>
                                  <tr>
                                    <td style="text-align:left">{{ trans('all.ppn10') }}</td>
                                    <td style="text-align:right"><b id="ppn_12bulan">Rp{{ number_format((Session::get('perusahaan_unitprice')-env('YEARLY_DISCOUNT',0))*Session::get('perusahaan_limitpegawai')*12*0.1, 0, ",", ".")}}</b></td>
                                  </tr>
                                  <tr style="font-size:16px">
                                    <td style="text-align:left">{{ trans('all.harga_total') }}</td>
                                    <td style="text-align:right"><b id="total_12bulan">Rp{{ number_format((Session::get('perusahaan_unitprice')-env('YEARLY_DISCOUNT',0))*Session::get('perusahaan_limitpegawai')*12*1.1, 0, ",", ".")}}</b></td>
                                  </tr>
                                </tbody>
                              </table>
                            </p>
                            <button type="submit" aria-hidden="true" name="periode" value="12" class="btn btn-primary">{{ trans('all.bayar')." ".trans('all.1_tahun') }}</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </form>
              @endif
            </div>
        </div>
    </div>
</div>
