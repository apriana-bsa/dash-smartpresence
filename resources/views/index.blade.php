@extends('layouts/master')
@section('title', trans('all.beranda'))
@section('content')
  {{--  <script src="https://cdn.klokantech.com/maptilerlayer/v1/index.js"></script>  --}}
  <script src="{{ asset('lib/js/showcaser.min.js') }}"></script>
  <script>
  @if(Session::get('message'))
    $(document).ready(function() {
      setTimeout(function() {
          toastr.options = {
              closeButton: true,
              progressBar: true,
              timeOut: 4000,
              extendedTimeOut: 4000,
              positionClass: 'toast-bottom-right'
          };
          toastr.info('{{ Session::get("message") }}', '{{ trans("all.pemberitahuan") }}');
      }, 500);
    });
  @endif

  @if(Session::get('msgwarning'))
      $(document).ready(function() {
          setTimeout(function() {
              toastr.options = {
                  closeButton: true,
                  progressBar: true,
                  timeOut: 4000,
                  extendedTimeOut: 4000,
                  positionClass: 'toast-bottom-right'
              };
              toastr.warning('{{ Session::get("msgwarning") }}', '{{ trans("all.pemberitahuan") }}');
          }, 500);
      });
  @endif

  @if(Session::get('error'))
    alertError('{{ Session::get('error') }}');
  @endif

  @if(Session::has('overkuota'))
      alertConfirmNotClose('{{ Session::get('overkuota') }}',
          function(){
              alertConfirmNotClose('{{ trans("all.overkuota_konfirmasi_delete_pegawai") }}',
                  function(){
                      window.location.href="{{ url('datainduk/pegawai/pegawai') }}";
                  },
                  function(){
                      window.open("{{env('URL_TUTORIAL_HAPUS_PEGAWAI')}}")
                  },
                  "Ya, Lanjut",
                  "Lihat Tutorial"
              )
          },
          function(){}
      );
  @endif

  @if(Session::get('welcome'))
    $(document).ready(function() {
      setTimeout(function() {
          toastr.options = {
              closeButton: true,
              progressBar: true,
              timeOut: 4000,
              extendedTimeOut: 4000,
              positionClass: 'toast-bottom-right'
          };
          toastr.info('{{ Session::get("welcome") }}', '{{ trans("all.loginberhasil") }}');
      }, 500);
    });
  @endif

  @if(Session::get('error'))
    $(document).ready(function() {
      var pesan = '{{ Session::get("error") }}';
      alertError(pesan);
  });
  @endif

  @if(Session::get('pesansukses'))
    $(document).ready(function() {
      var pesan = '{{ Session::get("pesansukses") }}';
      var data = pesan.split('|');
      alertConfirm(data[1],
          function(){
              window.location.href="{{ url('perusahaan/kirimulangkonfirmasi') }}/"+data[0];
          },
          function(){

          },
          "{{ trans('all.kirimulangemail') }}","{{ trans('all.tutup') }}"
      );
    });
  @endif

  function showTips(){
      Showcaser.showcase('{!! trans('all.selamatdatang').' '.Session::get('namauser_perusahaan').' '.trans('all.apakahandabutuhbantuan') !!}',"",{
          buttonText: "{{ trans('all.ya') }}",
          skipText: "{{ trans('all.tidak') }}",
          close: function(){
              setCookies('tips');
          }
      });

      var panduanorogram = document.getElementById("panduanprogram");
      Showcaser.showcase("{{ trans('all.iniadalahpanduananda') }}",
      panduanorogram,
      {
          shape: "rectangle",
          buttonText: "{{ trans('all.lanjut') }}",
          skipText: "{{ trans('all.tutup') }}",
          close: function(){
              setCookies('tips');
          }
      });
  }

  @if(Session::has('perusahaan_showguide'))
    //showTips();
    // cekCookies('tips');
  @endif

  {{--@if(Session::get('conf_totalpegawai'))--}}
    {{--$(document).ready(function() {--}}
      {{--setTimeout(function() {--}}
          {{--toastr.options = {--}}
              {{--closeButton: true,--}}
              {{--progressBar: true,--}}
              {{--timeOut: 4000,--}}
              {{--extendedTimeOut: 4000,--}}
              {{--positionClass: 'toast-bottom-right'--}}
          {{--};--}}
          {{--toastr.info('{{ Session::get('conf_totalpegawai') }}', '{{ trans("all.totalpegawai") }}');--}}
      {{--}, 500);--}}
    {{--});--}}
  {{--@endif--}}

  function goto(page){
    window.location.href=page;
  }

@if (Session::get('onboardingvideo') && Session::get('conf_webperusahaan',0))
  $(function () {
    $('#modalvideo').modal({
      keyboard: false,
      backdrop: "static"
    });
    $('#modalvideo').modal('show');
    $("#modalvideo").on('hidden.bs.modal', function (e) {
      $("#modalvideo iframe").attr("src", $("#modalvideo iframe").attr("src"));
      $.ajax({
          type: "GET",
          url: '{{ url('disable_video_onboarding') }}',
          data: '',
          cache: false,
          success: function(json) {
              console.log(json);
          }
      });
    });
  })
@endif

  </script>
  <style>
  .progress{
      margin-bottom:30px;
  }

  .info-box{
      display: block;
      min-height: 80px;
      background: #fff;
      width: 100%;
      box-shadow: 0 1px 1px rgba(0,0,0,0.1);
      border-radius: 2px;
      margin-bottom: 15px;
  }

  .info-box-icon {
      border-top-left-radius: 2px;
      border-top-right-radius: 0;
      border-bottom-right-radius: 0;
      border-bottom-left-radius: 2px;
      display: block;
      float: left;
      height: 80px;
      width: 80px;
      text-align: center;
      font-size: 45px;
      line-height: 72px;
      background: rgba(0,0,0,0.1);
  }

  .info-box-content {
      padding: 5px 10px;
      margin-left: 90px;
  }

  .info-box-text {
      display: block;
      font-size: 18px;
      /*white-space: nowrap;*/
      overflow: hidden;
      text-overflow: ellipsis;
      text-transform: uppercase;
  }

  .info-box-number {
      display: block;
      font-weight: bold;
      font-size: 28px;
  }

  .dataTables_wrapper{
      padding-bottom:0;
      margin-top:-8px;
  }

  .ibox-content{
      padding:16px;
  }

  #map {
      height: 460px;
  }

  #pac-input {
      background-color: #fff;
      font-family: Roboto;
      font-size: 15px;
      font-weight: 300;
      margin-left: 12px;
      padding: 0 11px 0 13px;
      text-overflow: ellipsis;
      width: 300px;
      z-index:99999999;
  }

  #pac-input:focus {
      border-color: #4d90fe;
  }

  .pac-container {
      font-family: Roboto;
  }
  </style>
  @if(Session::has('conf_webperusahaan') && Session::get('perusahaan_expired') == 'tidak')
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-12">
        @if(!Session::has('conf_webperusahaan'))
        <h2>{{ trans('all.beranda') }}</h2>
        @else
            <h3 style="margin-top:15px;margin-bottom:0">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="{{ url('/') }}">{{ trans('all.beranda_beranda') }}</a></li>
                    @if($datacd->tampil_riwayatdashboard == 'y')
                        <li><a href="{{ url('/riwayatberanda') }}">{{ trans('all.riwayat') }}</a></li>
                    @endif
                    @if(strpos(Session::get('hakakses_perusahaan')->pekerjaanuser, 'l') !== false)
                        <li><a href="{{ url('pekerjaaninput') }}">{{ trans('all.pekerjaan') }}</a></li>
                    @endif
                </ul>
            </h3>
        @endif
    </div>
  </div>
@endif

  <div class="wrapper wrapper-content animated fadeIn">
    @if(Session::has('conf_webperusahaan'))
      @if(Session::get('perusahaan_expired') == 'tidak')
          <div class="row">
              @if($deskripsibatasan != '')
                  <div class="col-lg-12">
                      <div class="alert alert-warning">
                          <i class="fa fa-warning"></i>
                          {{ $deskripsibatasan }}
                      </div>
                  </div>
              @endif
            <div class="col-lg-12 col-md-12 col-sm-12" style="padding:0">
                @if($datacd->tampil_3lingkaran == 'y')
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="info-box" onclick="return ke('{{ url('riwayat/'.str_replace('-', '', $currentdate)) }}')" style="cursor:pointer;">
                            <span class="info-box-icon"><img src="{{ url('lib/drawable-xhdpi/dashboard_logabsen.png') }}" width="30px" height="40px"></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ trans('all.beranda_riwayat') }}</span>
                                <span class="info-box-number">{{ $riwayat }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="info-box" onclick="return ke('{{ url('rekap/'.str_replace('-', '', $currentdate)) }}')" style="cursor:pointer;">
                            <span class="info-box-icon"><img src="{{ url('lib/drawable-xhdpi/dashboard_rekapabsen.png') }}" width="40px" height="40px"></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ trans('all.beranda_rekapitulasi') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="info-box" onclick="return ke('{{ url('alasan/'.str_replace('-', '', $currentdate)) }}')" style="cursor:pointer;">
                            <span class="info-box-icon"><img src="{{ url('lib/drawable-xhdpi/dashboard_alasan.png') }}" width="40px" height="40px"></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ trans('all.beranda_alasan') }}</span>
                                <span class="info-box-number">{{ $alasan }}</span>
                            </div>
                        </div>
                    </div>
                @endif
                @if($datacd->tampil_peta == 'y')
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="info-box" onclick="return ke('{{ url('peta/'.str_replace('-', '', $currentdate)) }}')" style="cursor:pointer;">
                            <span class="info-box-icon"><img src="{{ url('lib/drawable-xhdpi/peta.png') }}" width="40px" height="40px"></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ trans('all.beranda_peta') }}</span>
                                <span class="info-box-number">{{ $peta }}</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="col-lg-12"></div>
            @if($custom != '')
                @if(count($custom) > 0)
                    @for($i=0;$i<count($custom);$i++)
                      <div class="col-lg-3 col-md-6 col-sm-12">
                          <div class="ibox float-e-margins" onclick="return goto('customdashboard/{{ $custom[$i]['id'] }}/{{ str_replace('-', '', $currentdate) }}')" style="cursor:pointer;">
                              {{--<div class="ibox-content" style="background-color: #1ab394;color:#fff">--}}
                              <div class="ibox-content" style="background-color: {{ \App\Utils::getWarnaHex($custom[$i]['warna']) }};color:#fff;height:105px">
                                  <div class="row">
                                      <div class="col-md-3 col-xs-3">
                                          <i class="fa {{ $custom[$i]['icon'] }}" style="font-size: 50px"></i>
                                      </div>
                                      <div class="col-md-9 col-xs-9 pull-right">
                                          <span>{{ $custom[$i]['judul'] }}</span>
                                          <h1 class="no-margins">{{ $custom[$i]['total'] }}</h1>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                    @endfor
                @endif
            @endif
            @if($datacd->tampil_sudahbelumabsen == 'y')
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="ibox float-e-margins" onclick="return goto('sudahabsen/{{ str_replace('-', '', $currentdate) }}')" style="cursor:pointer;">
                        <div class="ibox-content" style="background-color: #1ab394;color:#fff;height:105px;">
                            <div class="row">
                                <div class="col-md-4 col-xs-4">
                                    <img src="{{ url('lib/img/sudahabsen.png') }}" width="65px" height="70px">
                                </div>
                                <div class="col-md-8 col-xs-8 pull-right">
                                    {{ trans('all.beranda_sudahabsen') }}
                                    <h1 class="no-margins">{{ $sudahabsen }}</h1>
                                    <div class="stat-percent font-bold">{{ $persensudahabsen }}%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="ibox float-e-margins" onclick="return goto('belumabsen/{{ str_replace('-', '', $currentdate) }}')" style="cursor:pointer;">
                        <div class="ibox-content" style="background-color: #ed5565;color:#fff;height:105px">
                            <div class="row">
                                <div class="col-md-4 col-xs-4">
                                    <img src="{{ url('lib/img/belumabsen.png') }}" width="65px" height="70px">
                                </div>
                                <div class="col-md-8 col-xs-8 pull-right">
                                    {{ trans('all.beranda_belumabsen') }}
                                    <h1 class="no-margins">{{ $belumabsen }}</h1>
                                    <div class="stat-percent font-bold">{{ $persenbelumabsen }}%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @if($datacd->tampil_terlambatdll == 'y')
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="ibox float-e-margins" onclick="return goto('terlambat/{{ str_replace('-', '', $currentdate) }}')" style="cursor:pointer;">
                        <div class="ibox-content" style="background-color: #f8ac59;color:#fff;height:105px;">
                            <div class="row">
                                <div class="col-md-4 col-xs-4">
                                    <img src="{{ url('lib/img/terlambat.png') }}" width="65px" height="70px">
                                </div>
                                <div class="col-md-8 col-xs-8 pull-right">
                                    {{ trans('all.terlambat') }}
                                    <h1 class="no-margins">{{ $terlambat }}</h1>
                                    <div class="stat-percent font-bold">{{ $persenterlambat }}%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="ibox float-e-margins" onclick="return goto('adadikantor/o')" style="cursor:pointer;">
                        <div class="ibox-content" style="background-color: #2E8A9C;color:#fff;height:105px">
                            <div class="row">
                                <div class="col-md-4 col-xs-4">
                                    <img src="{{ url('lib/img/adadikantor.png') }}" width="65px" height="70px">
                                </div>
                                <div class="col-md-8 col-xs-8 pull-right">
                                    {{ trans('all.beranda_adadikantor') }}
                                    <h1 class="no-margins">{{ $adadikantor }}</h1>
                                    <div class="stat-percent font-bold">{{ $persenadadikantor }}%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="ibox float-e-margins" onclick="return goto('ijintidakmasuk/o')" style="cursor:pointer;">
                        <div class="ibox-content" style="background-color: #23c6c8;color:#fff;height:105px;">
                            <div class="row">
                                <div class="col-md-4 col-xs-4">
                                    <img src="{{ url('lib/img/ijintidakmasuk.png') }}" width="65px" height="70px">
                                </div>
                                <div class="col-md-8 col-xs-8 pull-right">
                                    {{ trans('all.beranda_ijintidakmasuk') }}
                                    <h1 class="no-margins">{{ $ijintidakmasuk }}</h1>
                                    <div class="stat-percent font-bold">{{ $persenijintidakmasuk }}%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @if($datacd->tampil_pulangawaldll == 'y')
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="ibox float-e-margins" onclick="return goto('pulangawal/{{ str_replace('-', '', $currentdate) }}')" style="cursor:pointer;">
                        <div class="ibox-content" style="background-color: #ed5565;color:#fff;height:105px;">
                            <div class="row">
                                <div class="col-md-4 col-xs-4">
                                    <img src="{{ url('lib/img/pulangawal.png') }}" width="65px" height="70px">
                                </div>
                                <div class="col-md-8 col-xs-8 pull-right">
                                    {{ trans('all.beranda_pulangawal') }}
                                    <h1 class="no-margins">{{ $pulangawal }}</h1>
                                    <div class="stat-percent font-bold">{{ $persenpulangawal }}%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="ibox float-e-margins" onclick="return goto('datacapture/{{ str_replace('-', '', $currentdate) }}')" style="cursor:pointer;">
                        <div class="ibox-content" style="background-color: #44596e;color:#fff;height:105px;">
                            <div class="row">
                                <div class="col-md-4 col-xs-4">
                                    <img src="{{ url('lib/img/totalmesin.png') }}" width="65px" height="70px">
                                </div>
                                <div class="col-md-8 col-xs-8 pull-right">
                                    {{ trans('all.beranda_datacapture') }}
                                    <h1 class="no-margins" style="font-size: 47px;">{{ $mesindigunakan }}</h1>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @if($datacd->tampil_totalgrafik == 'y')
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="ibox float-e-margins" onclick="return goto('totalpegawai/aktif/o')" style="cursor:pointer;">
                        <div class="ibox-content" style="background-color: #1c84c6;color:#fff;height:105px;">
                            <div class="row">
                                <div class="col-md-4 col-xs-4">
                                    <img src="{{ url('lib/img/totalpegawai.png') }}" width="65px" height="70px">
                                </div>
                                <div class="col-md-8 col-xs-8 pull-right">
                                    {{ trans('all.beranda_totalpegawai') }}
                                    <h1 class="no-margins">{{ $totalpegawai }}</h1>
                                    <div class="stat-percent font-bold">
                                        <span title="{{ trans('all.tidakaktif') }}"><i class="fa fa-user-times"></i>&nbsp;{{ $totalpegawaitidakaktif }}&nbsp;&nbsp;</span>
                                        <span title="{{ trans('all.terhapus') }}"><i class="fa fa-trash"></i>&nbsp;{{ $totalpegawaiterhapus }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5>{{ trans('all.beranda_keteranganchart') }}</h5>
                        </div>
                        <div class="ibox-content">
                            <div class="row">
                                <div class="col-lg-9">
                                    <div class="flot-chart">
                                        <div class="flot-chart-content" id="flot-dashboard-chart"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <ul class="stat-list">
                                        <li>
                                            <h2 class="no-margins">@if($jummasuk == "") 0 @else {{ $jummasuk }} @endif</h2>
                                            <small>{{ trans('all.beranda_rataratamasuk') }}</small>
                                            <div class="stat-percent">{{ $rataratamasuk+0 }}%</div>
                                            <div class="progress progress-mini">
                                                <div style="width: {{ $rataratamasuk+0 }}%;" class="progress-bar"></div>
                                            </div>
                                        </li>
                                        <li>
                                            <h2 class="no-margins">@if($jumtidakmasuk == "") 0 @else {{ $jumtidakmasuk }} @endif</h2>
                                            <small>{{ trans('all.beranda_rataratatidakmasuk') }}</small>
                                            <div class="stat-percent">{{ $rataratatidakmasuk+0 }}%</div>
                                            <div class="progress progress-mini">
                                                <div style="width: {{ $rataratatidakmasuk+0 }}%;" class="progress-bar"></div>
                                            </div>
                                        </li>
                                        <li>
                                            <h2 class="no-margins">@if($jumterlambat == "") 0 @else {{ $jumterlambat }} @endif</h2>
                                            <small>{{ trans('all.beranda_ratarataterlambat') }}</small>
                                            <div class="stat-percent">{{ $ratarataterlambat+0 }}%</div>
                                            <div class="progress progress-mini">
                                                <div style="width: {{ $ratarataterlambat+0 }}%;" class="progress-bar"></div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            {{--@if($datacd->tampil_peta == 'y')
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-content" style="padding: 0;min-height:460px;">
                            <input id="pac-input" class="controls" type="text" placeholder="Search Box">
                            <div id="map"></div>
                            <button id="tombolpeta" style="display:none"></button>
                        </div>
                    </div>
                </div>
            @endif--}}
            <div class="col-lg-12"></div>
            @if($datacd->tampil_peringkat == 'y')
                @if(count($peringkatabsen) != 0)
                    @foreach($peringkatabsen as $key)
                        <div class="col-lg-4 col-md-12 col-sm-12" style="margin-bottom:25px;cursor:pointer;" onclick="ke('{{ url('peringkat/peringkatterbaik/o') }}')">
                            <div class="ibox-content text-center">
                                <h2>{{ trans('all.beranda_pegawaiterbaik') }}</h2>
                                <div class="m-b-md">
                                  <img class="circle-border" src="{{ url('foto/pegawai/'.$key->idpegawai) }}" width="120px" height="120px" style="border-radius: 50%;">
                                </div>
                                <h2 class="font-bold"><i class="fa fa-trophy fa-1x"></i>&nbsp;&nbsp;{{ $key->nama }}</h2>
                            </div>
                        </div>
                    @endforeach
                @endif
            @endif
            @if($datacd->tampil_harilibur == 'y')
                <div @if(count($peringkatabsen) == 0) class="col-lg-12 col-md-12 col-sm-12" @else class="col-lg-8 col-md-12 col-sm-12" @endif>
                  <div class="ibox float-e-margins">
                      <div class="ibox-content">
                        <h2>{{ trans('all.beranda_harilibur') }}</h2>
                        @if(count($harilibur) > 0)
                            <table class="table datatable table-condensed no-margins">
                              <thead>
                                <tr>
                                  <td class="alamat"><b>{{ trans('all.tanggal') }}</b></td>
                                  <td class="keterangan"><b>{{ trans('all.keterangan') }}</b></td>
                                </tr>
                              </thead>
                              <tbody>
                                @foreach($harilibur as $key)
                                  <tr>
                                    <td>{{ $key->tanggalawal.' - '.$key->tanggalakhir }}</td>
                                    <td>{!! $key->keterangan !!}</td>
                                  </tr>
                                @endforeach
                              </tbody>
                            </table>
                            <br>
                            <center><a href='datainduk/absensi/harilibur'>{{ trans('all.selengkapnya') }}</a></center>
                        @else
                            <center>{{ trans('all.nodata') }}</center>
                        @endif
                      </div>
                  </div>
                </div>
            @endif
          </div>
      @else
          {{-- @yield('content') --}}
          @include('layouts/component_index_expired')
      @endif
    @else
      <div class="row">
        <div class="col-lg-12">
          <div class="ibox float-e-margins">
            <div class="ibox-content text-center p-md">
              <h2>
                {{ trans('all.selamatdatang') }}
              </h2>
              @if(Session::has('userbaru_perusahaan'))
                  <p>
                      {{ trans('all.keteranganuserbaru') }}
                  </p>
                  <a href="{{ url('daftar/kirimulang/'.Session::get('iduser_perusahaan')) }}">{{ trans('all.kirimulangemail') }}</a>
              @else
                  @if(Session::get('conf_totalperusahaan') == 0)
                    <p>
                      {{ trans('all.andatidakberelasidenganperusahaanmanapun') }}
                    </p>
                  @else
                    {{ trans('all.silahkanpilihperusahaan') }} /
                  @endif
                    <a id="tambahperusahaanbaru" href="{!! url('tambahperusahaanbaru') !!}">{{ trans('all.tambahkanperusahaan') }}</a>
              @endif
            </div>
          </div>
        </div>
      </div>
    @endif

    <script>
        function setCookies(jenis){
            //alert('tester');
            $.ajax({
                type: "GET",
                url: '{{ url('cookie/set') }}/'+jenis,
                data: '',
                cache: false,
                success: function(html) {
                    //console.log(html);
                }
            });
        }

        function startShowcaser() {
            Showcaser.showcase('{{ trans('all.selamatdatangdismartpresence') }}',"",{
                buttonText: "{{ trans('all.lanjut') }}",
                skipText: "{{ trans('all.tutup') }}",
                close: function(){
                    setCookies('showcase');
                }
            });

            var pilihperusahaan = document.getElementById("pilihperusahaan");
            Showcaser.showcase(
                "{{ trans('all.pilihperusahaan') }}<br><span style='font-size:12px'>{{ trans('all.keteranganshowcase1') }}</span>",
                pilihperusahaan,
                {
                    shape: "rectangle",
                    buttonText: "{{ trans('all.lanjut') }}",
                    skipText: "{{ trans('all.tutup') }}",
                    close: function(){
                        setCookies('showcase');
                    }
                });

            var tambahperusahaan = document.getElementById("tambahperusahaanbaru");
            Showcaser.showcase(
                "{{ trans('all.buatperusahaan') }}<br><span style='font-size:12px'>{{ trans('all.keteranganshowcase2') }}</span>",
                tambahperusahaan,
                {
                    shape: "rectangle",
                    buttonText: "{{ trans('all.lanjut') }}",
                    skipText: "{{ trans('all.tutup') }}",
                    close: function(){
                        setCookies('showcase');
                    }
                });
        }

        @if(Session::get('welcome'))
            @if(!Session::has('conf_webperusahaan'))
                //setTimeout(startShowcaser, 1000);
            @endif
        @endif

        // function cekCookies(jenis){
        //     //alert('tester');
        //     if(jenis != undefined) {
        //         $.ajax({
        //             type: "GET",
        //             url: '{{ url('cookie/cek/showcase') }}/' + jenis,
        //             data: '',
        //             cache: false,
        //             success: function (html) {
        //                 //console.log(html);
        //                 if (html == 'unset') {
        //                     //if(html)
        //                     //console.log('start show case');
        //                     if (jenis == 'showcase') {
        //                         setTimeout(startShowcaser, 500);
        //                     } else if (jenis == 'tips') {
        //                         setTimeout(showTips, 500);
        //                     }
        //                 }
        //             }
        //         });
        //     }
        // }

        // setTimeout(cekCookies(), 1000);
    </script>

    <!-- Modal Onboarding Video -->
    <div class="modal fade" id="modalvideo" role="dialog">
        <div class="modal-dialog modal-sm" style="width:605px">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">{{ trans('onboarding.onboarding_video') }}</h4>
                </div>
                <div class="modal-body body-modal" id="modalbody-video" style="height:330px; width: 605px; padding: 2px">
                    <iframe width="600" height="330" src="https://www.youtube.com/embed/VJDzD5joG1I" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
                <div class="modal-footer">
                    <table width="100%">
                        <tr>
                            <td align="right" style="padding:0">
                                <button class="btn btn-primary" id="tutupmodal" data-dismiss="modal"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.tutup') }}</button>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Onboarding Video -->

    <!-- Modal peta-->
    <a href="" id="showmodalpeta" data-toggle="modal" data-target="#modalpeta" style="display:none"></a>
    <div class="modal modalpeta fade" id="modalpeta" role="dialog" tabindex='-1'>
        <div class="modal-dialog modal-sm" style="width:435px">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">{{ trans('all.peta') }}</h4>
                </div>
                <div class="modal-body body-modal" id="modalbody-peta" style="max-height:480px;overflow: auto;">
                    <div id="isimodalpeta"></div>
                </div>
                <div class="modal-footer">
                    <table width="100%">
                        <tr>
                            <td align="right" style="padding:0">
                                <button class="btn btn-primary" id="tutupmodal" data-dismiss="modal"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.tutup') }}</button>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal peta-->

  <!-- Flot -->
  <script src="{{ asset('lib/js/plugins/flot/jquery.flot.js') }}"></script>
  <script src="{{ asset('lib/js/plugins/flot/jquery.flot.tooltip.min.js') }}"></script>
  <script src="{{ asset('lib/js/plugins/flot/jquery.flot.spline.js') }}"></script>
  <script src="{{ asset('lib/js/plugins/flot/jquery.flot.resize.js') }}"></script>
  <script src="{{ asset('lib/js/plugins/flot/jquery.flot.pie.js') }}"></script>
  <script src="{{ asset('lib/js/plugins/flot/jquery.flot.symbol.js') }}"></script>
  <script src="{{ asset('lib/js/plugins/flot/jquery.flot.time.js') }}"></script>
  <script src="{{ asset('lib/js/plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>
  <script type="text/javascript">
  @if($datacd->tampil_totalgrafik == 'y')
      var dataTerlambat = [
          @if(Session::get('conf_webperusahaan') != '')
              @foreach($grafik as $key)
                [gd({{ $key->tanggal }}), {{ $key->jum_terlambat}}],
              @endforeach
          @endif
      ];

      var dataMasuk = [
          @if(Session::get('conf_webperusahaan') != '')
                @foreach($grafik as $key)
                [gd({{ $key->tanggal }}), {{ $key->jum_masuk }}],
                @endforeach
          @endif
      ];

      var dataset = [
          {
              label: "{{ trans('all.beranda2_hadir') }}",
              data: dataMasuk,
              color: "#1ab394",
              bars: {
                  show: true,
                  align: "center",
                  barWidth: 24 * 60 * 60 * 600,
                  lineWidth:0
              }
          },{
              label: "{{ trans('all.terlambat') }}",
              data: dataTerlambat,
              yaxis: 2,
              color: "#1C84C6",
              lines: {
                  lineWidth:1,
                  show: true,
                  fill: true,
                  fillColor: {
                      colors: [{
                          opacity: 0.2
                      }, {
                          opacity: 0.4
                      }]
                  }
              },
              splines: {
                  show: false,
                  tension: 0.6,
                  lineWidth: 1,
                  fill: 0.1
              },
          }
      ];

      var options = {
          xaxis: {
              mode: "time",
              tickSize: [1, "day"],
              tickLength: 0,
              axisLabel: "Date",
              axisLabelUseCanvas: true,
              axisLabelFontSizePixels: 12,
              axisLabelFontFamily: 'Arial',
              axisLabelPadding: 10,
              color: "#d5d5d5"
          },
          yaxes: [{
              position: "left",
              max: {{ $totalpegawai }},
              color: "#d5d5d5",
              axisLabelUseCanvas: true,
              axisLabelFontSizePixels: 12,
              axisLabelFontFamily: 'Arial',
              axisLabelPadding: 3
          }, {
              position: "right",
              max: {{ $totalpegawai }},
              clolor: "#d5d5d5",
              axisLabelUseCanvas: true,
              axisLabelFontSizePixels: 12,
              axisLabelFontFamily: ' Arial',
              axisLabelPadding: 67
          }
          ],
          legend: {
              noColumns: 1,
              labelBoxBorderColor: "#000000",
              position: "nw"
          },
          grid: {
              hoverable: true,
              borderWidth: 0
          }
      };

      var previousPoint = null, previousLabel = null;

    $.plot($("#flot-dashboard-chart"), dataset, options);

    function gd(year, month, day) {
      return new Date(year, month - 1, day).getTime();
    }
  @endif

  //init peta
//   var locations = [];
//   var map = '';
//   var markerCluster = null;
//   var markerClusterBingkai = null;
//   var markers = [];
//   var markersbingkai = [];
//   var lokasi;
//   @if($lokasi != '')
//       lokasi = [
//           @foreach($lokasi as $key)
//               [{{ $key->lat }}, {{ $key->lon }}, '{{ $key->nama }}'],
//           @endforeach
//       ];
//   @endif

//   function initMap() {
//       map = new google.maps.Map(document.getElementById('map'), {
//           center: {lat: -4.653079918274038, lng: 117.7734375},
//           zoom: 5,
//           mapTypeId: 'roadmap',
//           gestureHandling: 'greedy',
//           fullscreenControl: false,
//           styles: styleGoogleMaps
//       });

//       var mapMaxZoom = 13;

//       // Create the search box and link it to the UI element.
//       var input = document.getElementById('pac-input');
//       var searchBox = new google.maps.places.SearchBox(input);
//       map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

//       // Bias the SearchBox results towards current map's viewport.
//       map.addListener('bounds_changed', function () {
//           searchBox.setBounds(map.getBounds());
//       });

//       var marker_lokasi, i;

//       var icon = {
//           url: '{{ url('lib/drawable-xhdpi/perusahaan.png') }}', // url
//           scaledSize: new google.maps.Size(24, 24), // scaled size
//           origin: new google.maps.Point(0, 0), // origin
//           anchor: new google.maps.Point(10, 35) // anchor
//       };

//       for (i = 0; i < lokasi.length; i++) {
//           marker_lokasi = new google.maps.Marker({
//               position: new google.maps.LatLng(lokasi[i][0], lokasi[i][1]),
//               map: map,
//               icon: icon
//           });

//           google.maps.event.addListener(marker_lokasi, 'click', (function (marker_lokasi, i) {
//               return function () {
//                   //console.log(lokasi[i][2])
//                   alertInfo('{{ trans('all.lokasi') }} ' + lokasi[i][2]);
//                   /*infowindow.setContent(lokasi[i][0]);
//                    infowindow.open(map, marker_lokasi);*/
//               }
//           })(marker_lokasi, i));
//       }

//       searchBox.addListener('places_changed', function () {
//           var places = searchBox.getPlaces();

//           if (places.length == 0) {
//               return;
//           }

//           // Clear out the old markers.
//           if (markers != '') {
//               //markers.setMap(null);
//           }

//           // For each place, get the icon, name and location.
//           var bounds = new google.maps.LatLngBounds();
//           places.forEach(function (place) {
//               if (!place.geometry) {
//                   console.log("Returned place contains no geometry");
//                   return;
//               }

//               // Create a marker for each place.
//               markers = new google.maps.Marker({
//                   //position: place.geometry.location, //untuk kasih marker
//                   map: map
//               });

//               markers.addListener('click', function (event) {
//                   //getlatlon(event);
//               });

//               if (place.geometry.viewport) {
//                   // Only geocodes have viewport.
//                   bounds.union(place.geometry.viewport);
//               } else {
//                   bounds.extend(place.geometry.location);
//               }
//           });
//           map.fitBounds(bounds);
//       });

//   }

  function modalMarkerPeta(marker,startfrom){

      $('#isimodalpeta').append('<center class="spinner-loadpeta"><i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i></center>');
      setTimeout(function(){
          $.ajax({
              type: "GET",
              url: '{{ url('generatecsrftoken') }}',
              data: '',
              cache: false,
              success: function(token){
                  var dataString = 'marker='+marker+'&startfrom='+startfrom+'&_token='+token;
                  $.ajax({
                      type: "POST",
                      url: '{{ url('modalmarkerpeta') }}',
                      data: dataString,
                      cache: false,
                      success: function(html){
                          //console.log(html);
                          if(startfrom == 'o') {
                              $('#isimodalpeta').html('');
                              $('#showmodalpeta').trigger('click');
                              setTimeout(function () {
                                  $('#isimodalpeta').html(html);
                              }, 200);
                          }else{
                              setTimeout(function () {
                                  $('.spinner-loadpeta').remove();
                                  $('#isimodalpeta').append(html);
                              }, 200);
                          }
                      }
                  });
              }
          });
      },500);
      return false;
  }

  $(function() {
      //console.log(cekSelisihTanggal('01/01/2019','10/02/2019',300));
      $('.datatable').DataTable({
          responsive: true,
          scrollX: true,
          paging:   false,
          ordering: false,
          info:     false,
          searching: false
      });

//       setTimeout(function(){ $('#tombolpeta').trigger('click'); },100);
//       $('#tombolpeta').click(function(){
//           initMap();
//       });

//       setTimeout(function(){
//           $.ajax({
//               type: "GET",
//               url: '{{ url('lokasiabsen') }}',
//               data: '',
//               cache: false,
//               success: function(html){
//                   $('#loading-saver').css('display', 'none');
//                   //hapus marker lama
//                   if (markerCluster!=null) {
//                       markerCluster.clearMarkers();
//                   }
//                   if (markerClusterBingkai!=null) {
//                       markerClusterBingkai.clearMarkers();
//                   }

//                   //set lokasi map ke awal
//                   map.setCenter({lat: -4.653079918274038, lng:117.7734375});

//                   if(html != ''){
//                       locations = html;

//                       //kasih marker
//                       {{--markers = locations.map(function(location) {--}}
// {{--                          console.log('{{ url('fotologabsen') }}/'+location.id+'/thumb');--}}
//                           {{--var iconlogabsen = {--}}
//                               {{--url: '{{ url('fotologabsens') }}/'+location.id+'/thumb',--}}
// {{--                              url: '{{ url('lib/drawable-xhdpi/flag_orang_absen.png') }}',--}}
//                               {{--scaledSize: new google.maps.Size(40, 40), // scaled size--}}
// {{--//                    origin: new google.maps.Point(0, 0), // origin--}}
//                               {{--anchor: new google.maps.Point(20, 48) // anchor--}}
//                           {{--};--}}
//                           {{--//console.log(location);--}}
//                           {{--return new google.maps.Marker({--}}
//                               {{--position: location,--}}
//                               {{--map: map,--}}
//                               {{--flag: 'log',--}}
//                               {{--id: location.id,--}}
//                               {{--icon: iconlogabsen,--}}
//                               {{--zIndex: 99999999--}}
//                           {{--});--}}
//                       {{--});--}}

//                       //marker bingkai(frame)
//                       {{--var iconbingkai = {--}}
// {{--//                          url: 'http://i.stack.imgur.com/KOh5X.png',--}}
//                           {{--url: '{{ url('lib/drawable-xhdpi/KOh5X.png') }}',--}}
//                           {{--scaledSize: new google.maps.Size(50, 65), // scaled size--}}
// {{--//                    origin: new google.maps.Point(0, 0), // origin--}}
//                           {{--anchor: new google.maps.Point(25, 53) // anchor--}}
//                       {{--};--}}
//                       {{--//kasih marker baru bingkai--}}
//                       {{--markersbingkai = locations.map(function(location) {--}}
//                           {{--//console.log(location);--}}
//                           {{--return new google.maps.Marker({--}}
//                               {{--position: location,--}}
//                               {{--map: map,--}}
//                               {{--icon: iconbingkai,--}}
//                               {{--zIndex: 999999--}}
//                           {{--});--}}
//                       {{--});--}}

//                       {{--markerClusterBingkai = new MarkerClusterer(map, markersbingkai,--}}
//                           {{--{zoomOnClick: false, imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'});--}}

//                       var icon = {
//                           url: '{{ url('lib/drawable-xhdpi/flag_orang_absen.png') }}', // url
//                           scaledSize: new google.maps.Size(40, 40), // scaled size
//                           origin: new google.maps.Point(0,0), // origin
//                           anchor: new google.maps.Point(10, 35) // anchor
//                       };

//                       //kasih marker baru
//                       markers = locations.map(function(location) {
//                           //console.log(location);
//                           return new google.maps.Marker({
//                               position: location,
//                               map: map,
//                               flag: 'log',
//                               id: location.id,
//                               icon: icon
//                           });
//                       });

//                       for(var i=0;i<markers.length;i++) {
//                           google.maps.event.addDomListener(markers[i], 'click', function() {
//                               modalMarkerPeta(this.id,'o');
//                           });
//                       }

//                       markerCluster = new MarkerClusterer(map, markers,
//                           {zoomOnClick: false, imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'});

//                       google.maps.event.addListener(markerCluster, "clusterclick", function (cluster) {
// //                          console.log(cluster.markers_[0].id);
//                           var _marker = '';
//                           for(var i=0;i<cluster.markers_.length;i++){
//                               //console.log(cluster.markers_[i].id);
//                               _marker += ','+cluster.markers_[i].id;
//                           }
//                           modalMarkerPeta(_marker.substring(1),'o');
//                       });


//                       //console.log(markerCluster.clusters_);
//                       //markerCluster = new MarkerClusterer(map, markers, mcOptions);
//                   }
//               }
//           });
//       },200);
  });
  </script>
  {{--<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>
  <script src="https://maps.googleapis.com/maps/api/js?key={{ Session::get('conf_googlemapsapi') }}&libraries=places"
			async defer></script>--}}
@stop
