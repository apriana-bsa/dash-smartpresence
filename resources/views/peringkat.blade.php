@extends('layouts/master')
@section('title', trans('all.beranda'))
@section('content')

  <script>
  $(document).ready(function() {
      setTimeout(function(){ $('#filterAtribut').css('display', ''); }, 1000);

      $('#filterAtribut').BootSideMenu({side:"right"});

      $('#resetfilter').click(function(){
          $('input:checkbox').removeAttr('checked');
      });

      var win = $(window);

      var run = true;
      // Each time the user scrolls
      win.scroll(function() {
          // End of the document reached?
          if ($(document).height() - win.height() == win.scrollTop()) {
              //var detail = $('#bantuan').attr('detail');
              //var startfrom = $('#bantuan').attr('startfrom');
              if(run == true) {
                  run = false;
                  //more(detail, startfrom);
                  $('.loadmorebutton').trigger('click');
                  //alert('ok');
              }
          }
      });
  });

  function kembali(){
    window.location.href="{{ url('/') }}";
  }

  function more(jenis,startfrom){
      showSpinner();
      var url = '{{ url('peringkat') }}/'+jenis+'/'+startfrom;
      $.ajax({
          type: "GET",
          url: url,
          data: '',
          cache: false,
          success: function(html){
              hideSpinner();
              $('#kelompoktombol').remove();
              $('#moredata').append(html);
          }
      });
      return false;
  }

  function showSpinner(){
      $('#loading-saver').css('display', '');
      $('#spinner-loadmore').css('display', '');
  }

  function hideSpinner(){
      $('#loading-saver').css('display', 'none');
      $('#spinner-loadmore').css('display', 'none');
  }

  function pencarianDetail(){

      $('#moredata').html('');
      showSpinner();

      var pencarian = $('#keyword_pencarian').val();
      var tanggal = $('#tanggal_pencarian').val();
      var tanggalkalender = $('#tanggal_kalender').val();
      var jenis = $('#jenis_pencarian').val();
      var token = $('#token_pencarian').val();

      var dataString =  '_token=' + token + '&pencarian=' + pencarian + '&tanggal=' + tanggal + '&tanggalkalender=' + tanggalkalender + '&jenis=' + jenis;
      $.ajax({
          type: "POST",
          url: '{{ url('pencariandetail') }}',
          data: dataString,
          cache: false,
          success: function(html) {
              hideSpinner();
              $('#moredata').html('').append(html);
          }
      });
      return false;
  }
  </script>
  <style>
  span{
    cursor:default;
  }

  .tdpulangawal, .tdterlambat {
      display:inline-block;
      padding-bottom:5px;
      width:200px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
  }

  .tdPeringkat{
      padding-bottom:5px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
  }

  .ibox-content{
      padding:20px;
  }

  .tdfilter{
      padding:5px;
  }
  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-8">
      <h2>{{ trans('all.peringkat') }}</h2>
    </div>
    <div class="col-lg-4">
        <div class="search-form">
            <form action="{{ url('pencariandetail') }}" method="post" onsubmit="return pencarianDetail()">
                <input type="hidden" id="token_pencarian" name="token" value="{{ csrf_token() }}">
                <input type="hidden" id="jenis_pencarian" name="jenis" value="{{ $jenis }}">
                <input type="hidden" id="tanggal_pencarian" name="tanggal" value="">
                <input type="hidden" id="tanggal_kalender" name="tanggalkalender" value="">
                <div class="input-group" style="margin-top:23px">
                    <input type="text" @if(Session::has($jenis.'_pencarian_detail')) value="{{ Session::get($jenis.'_pencarian_detail') }}" @endif placeholder="{{ trans('all.pencarian') }}..." autocomplete="off" class="form-control input-sm" name="pencarian" id="keyword_pencarian">
                    <div class="input-group-btn">
                        <input type="submit" class="btn btn-sm btn-primary" value="{{ trans('all.cari') }}">
                        <a href="{{ url('peringkat/'.$jenis.'/excel') }}" class="btn btn-sm btn-success">{{ trans('all.ekspor') }}</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
  </div>

  <div id='filterAtribut' style="display: none">
      <form action='{{ url('peringkat/'.$jenis.'/o') }}' method='post'>
          {{ csrf_field() }}
          <table width=100% style="margin-bottom: 60px">
              <tr>
                  <td colspan='2' class='tdheader' style='height:61px;background: #f3f3f4;color:#676a6c;font-size:24px;padding-left:15px;'><i class='fa fa-filter'></i> {{ trans('all.filter') }}</td>
              </tr>
              <tr>
                  <td class="tdfilter">
                      <span style="padding-left:10px">{{ trans('all.jamkerja') }}</span>
                      <div style="padding-left:15px">
                          <table width="100%">
                              <tr>
                                  <td valign="top" style="width:10px;" class="tdfilter">
                                      @if(Session::has($jenis.'_jamkerja'))
                                          <input type="radio" @if(Session::get($jenis.'_jamkerja') == 'full') checked @endif id="jamkerjafull" name="jamkerja" value="full">
                                      @else
                                          <input type="radio" id="jamkerjafull" name="jamkerja" value="full">
                                      @endif
                                  </td>
                                  <td valign="top" class="tdfilter">
                                      <span onclick="spanClick('jamkerjafull')">{{ trans('all.full') }}</span>
                                  </td>
                              </tr>
                              <tr>
                                  <td valign="top" style="width:10px;" class="tdfilter">
                                      @if(Session::has($jenis.'_jamkerja'))
                                          <input type="radio" @if(Session::get($jenis.'_jamkerja') == 'shift') checked @endif id="jamkerjashift" name="jamkerja" value="shift">
                                      @else
                                          <input type="radio" id="jamkerjashift" name="jamkerja" value="shift">
                                      @endif
                                  </td>
                                  <td valign="top" class="tdfilter">
                                      <span onclick="spanClick('jamkerjashift')">{{ trans('all.shift') }}</span>
                                  </td>
                              </tr>
                          </table>
                      </div>
                  </td>
              </tr>
              <tr>
                  <td class="tdfilter">
                      <span style="padding-left:10px">{{ trans('all.kategorijamkerja') }}</span>
                      <div style="padding-left:15px">
                          @if($jamkerjakategori != '')
                              @foreach($jamkerjakategori as $key)
                                  {{ $checked = false }}
                                  @if(Session::has($jenis.'_kategorijamkerja'))
                                      @for($i=0;$i<count(Session::get($jenis.'_kategorijamkerja'));$i++)
                                          @if($key->id == Session::get($jenis.'_kategorijamkerja')[$i])
                                              <span style="display:none">{{ $checked = true }}</span>
                                          @endif
                                      @endfor
                                  @endif
                                  <table width="100%">
                                      <tr>
                                          <td valign="top" style="width:10px;" class="tdfilter">
                                              <input type="checkbox" id="kategorijamkerja_{{ $key->id }}" @if($checked == true) checked @endif name="kategorijamkerja[]" value="{{ $key->id }}">
                                          </td>
                                          <td valign="top" class="tdfilter">
                                              <span onclick="spanClick('kategorijamkerja_{{ $key->id }}')">{{ $key->nama }}</span>
                                          </td>
                                      </tr>
                                  </table>
                              @endforeach
                          @endif
                      </div>
                  </td>
              </tr>
              @if(isset($atribut))
                  @foreach($atribut as $key)
                      @if(count($key->atributnilai) > 0)
                          <tr>
                              <td class="tdfilter">
                                  <span style="padding-left:10px">{{ $key->atribut }}</span>
                                  @foreach($key->atributnilai as $atributnilai)
                                      @if(Session::has($jenis.'_atributfilter'))
                                          {{ $checked = false }}
                                          @for($i=0;$i<count(Session::get($jenis.'_atributfilter'));$i++)
                                              @if($atributnilai->id == Session::get($jenis.'_atributfilter')[$i])
                                                  <span style="display:none">{{ $checked = true }}</span>
                                              @endif
                                          @endfor
                                          <div style="padding-left:15px">
                                              <table width="100%">
                                                  <tr>
                                                      <td class="tdfilter" valign="top" style="width:10px;">
                                                          <input type="checkbox" id="atributnilai_{{ $atributnilai->id }}" @if($checked == true) checked @endif name="atributnilai[]" value="{{ $atributnilai->id }}">
                                                      </td>
                                                      <td class="tdfilter" valign="top">
                                                          <span onclick="spanClick('atributnilai_{{ $atributnilai->id }}')">{{ $atributnilai->nilai }}</span>
                                                      </td>
                                                  </tr>
                                              </table>
                                          </div>
                                      @else
                                          <div style="padding-left:15px">
                                              <table width="100%">
                                                  <tr>
                                                      <td class="tdfilter" valign="top" style="width:10px;">
                                                          <input type="checkbox" id="atributnilai_{{ $atributnilai->id }}" name="atributnilai[]" value="{{ $atributnilai->id }}">
                                                      </td>
                                                      <td class="tdfilter" valign="top">
                                                          <span onclick="spanClick('atributnilai_{{ $atributnilai->id }}')">{{ $atributnilai->nilai }}</span>
                                                      </td>
                                                  </tr>
                                              </table>
                                          </div>
                                      @endif
                                  @endforeach
                              </td>
                          </tr>
                      @endif
                  @endforeach
              @endif
          </table>
          <div style="height:60px;position: fixed;bottom: 0; background-color: #fff">
              <table style="margin-top:10px">
                  <tr>
                      <td class='tdfilter'>
                          <button id="submitfilter" type='submit' class="ladda-button btn btn-primary slide-left"><span class="label2">{{ trans('all.lanjut') }}</span> <span class="spinner"></span></button>
                      </td>
                      <td>
                          <button id="resetfilter" type='button' class="ladda-button btn btn-primary slide-left"><span class="label2">{{ trans('all.bersihkan') }}</span> <span class="spinner"></span></button>
                      </td>
                  </tr>
              </table>
          </div>
      </form>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <ul class="nav nav-tabs">
            <li @if($jenis == 'peringkatterbaik') class="active" @endif><a href="{{ url('peringkat/peringkatterbaik/o') }}">{{ trans('all.terbaik') }}</a></li>
            <li @if($jenis == 'peringkatterlambat') class="active" @endif><a href="{{ url('peringkat/peringkatterlambat/o') }}">{{ trans('all.terlambat') }}</a></li>
            <li @if($jenis == 'peringkatpulangawal') class="active" @endif><a href="{{ url('peringkat/peringkatpulangawal/o') }}">{{ trans('all.pulangawal') }}</a></li>
            <li @if($jenis == 'peringkatlamakerja') class="active" @endif><a href="{{ url('peringkat/peringkatlamakerja/o') }}">{{ trans('all.lamakerja') }}</a></li>
            <li @if($jenis == 'peringkatlamalembur') class="active" @endif><a href="{{ url('peringkat/peringkatlamalembur/o') }}">{{ trans('all.lamalembur') }}</a></li>
        </ul>
        <br>
        <div class="ibox float-e-margins" id="moredata">
            @if($data != '')
                @if(count($data) > 0)
                    @foreach($data as $key)
                        <div class="col-lg-4 col-md-6 col-sm-12">
                            <div class="ibox-content" style="margin-bottom:20px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">
                                @if($jenis == 'peringkatterbaik')
                                    <div style="position:absolute;right:15px;top:0;padding:6px">
                                        <span class="label">#{{ $key->peringkat }}</span>
                                    </div>
                                @endif
                                <div class="col-md-3 col-sm-12" style="padding-left:0;padding-bottom:10px;min-width:110px;max-width:110px">
                                    <center>
                                        <a href="{{ url('fotonormal/pegawai/'.$key->idpegawai) }}" title="{{ $key->namapegawai }}" data-gallery="">
                                            <img src="{{ url('foto/pegawai/'.$key->idpegawai) }}" width="110px" height="110px" style="border-radius:50%">
                                        </a>
                                    </center>
                                </div>
                                <div class="col-md-9 col-sm-12">
                                    @if($jenis == 'peringkatterbaik')
                                        <table width="100%">
                                            <tr>
                                                <td class="tdPeringkat" colspan="2">
                                                    <b title='{{ $key->namapegawai }}'>
                                                        {!! $key->nama !!}
                                                    </b>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="tdPeringkat" style="width:95px;padding-bottom:3px;">{{ trans('all.masukkerja') }}</td>
                                                <td class="tdPeringkat">&nbsp;&nbsp;:&nbsp;&nbsp;{{ $key->masukkerja.' '.strtolower(trans('all.hari')) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="tdPeringkat" style="padding-bottom:3px;">{{ trans('all.terlambat') }}</td>
                                                <td class="tdPeringkat">&nbsp;&nbsp;:&nbsp;&nbsp;{{ $key->terlambat.' '.trans('all.kali').' ('.\App\Utils::sec2pretty($key->terlambatlama).')' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="tdPeringkat" style="padding-bottom:3px;">{{ trans('all.pulangawal') }}</td>
                                                <td class="tdPeringkat">&nbsp;&nbsp;:&nbsp;&nbsp;{{ $key->pulangawal.' '.trans('all.kali').' ('.\App\Utils::sec2pretty($key->pulangawallama).')' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="tdPeringkat" style="padding-bottom:3px;">{{ trans('all.lamakerja') }}</td>
                                                <td class="tdPeringkat">&nbsp;&nbsp;:&nbsp;&nbsp;{{ \App\Utils::sec2pretty($key->lamakerja) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="tdPeringkat" style="padding-bottom:3px;">{{ trans('all.lamalembur') }}</td>
                                                <td class="tdPeringkat">&nbsp;&nbsp;:&nbsp;&nbsp;{{ \App\Utils::sec2pretty($key->lamalembur) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="tdPeringkat" style="padding-bottom:3px;" colspan="2">
                                                    <i style="cursor: pointer;font-size: 1.2em;" title="{{ trans('all.profil') }}" class="fa fa-user" onclick="return rincian('profil',{{ $key->idpegawai }})"></i>&nbsp;
                                                    <i style="cursor: pointer;font-size: 1.2em;" title="{{ trans('all.logabsen') }}" class="fa fa-calendar-o" onclick="return rincian('logabsen',{{ $key->idpegawai }})"></i>&nbsp;
                                                    <i style="cursor: pointer;font-size: 1.2em;" title="{{ trans('all.rekapabsen') }}" class="fa fa-calendar" onclick="return rincian('rekapabsen',{{ $key->idpegawai }})"></i>
                                                </td>
                                            </tr>
                                        </table>
                                    @elseif($jenis == 'peringkatterlambat')
                                        <table width="100%">
                                            <tr>
                                                <td class="tdterlambat"><b title='{{ $key->namapegawai }}'>{!! $key->nama !!}</b></td>
                                            </tr>
                                            <tr>
                                                <td class="tdterlambat" style='height:23px;padding-bottom:5px;width:210px;white-space:nowrap;overflow:hidden;text-overflow: ellipsis'><span class="label label-primary" title="{{ $key->atribut }}">{{ $key->atribut }}</span></td>
                                            </tr>
                                            <tr>
                                                <td class="tdterlambat">{{ trans('all.terlambat').' '.$key->terlambat.' '.trans('all.kali').' ('.\App\Utils::sec2pretty($key->terlambatlama).')' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="tdterlambat" style="padding-bottom:3px;" colspan="2">
                                                    <i style="cursor: pointer;font-size: 1.2em;" title="{{ trans('all.profil') }}" class="fa fa-user" onclick="return rincian('profil',{{ $key->idpegawai }})"></i>&nbsp;
                                                    <i style="cursor: pointer;font-size: 1.2em;" title="{{ trans('all.logabsen') }}" class="fa fa-calendar-o" onclick="return rincian('logabsen',{{ $key->idpegawai }})"></i>&nbsp;
                                                    <i style="cursor: pointer;font-size: 1.2em;" title="{{ trans('all.rekapabsen') }}" class="fa fa-calendar" onclick="return rincian('rekapabsen',{{ $key->idpegawai }})"></i>
                                                </td>
                                            </tr>
                                        </table>
                                    @elseif($jenis == 'peringkatpulangawal')
                                        <table width="100%">
                                            <tr>
                                                <td class="tdpulangawal" colspan="2" style='padding-bottom:5px;width:220px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;'><b title='{{ $key->namapegawai }}'>{!! $key->nama !!}</b></td>
                                            </tr>
                                            <tr>
                                                <td class="tdpulangawal" style='height:23px;padding-bottom:5px;width:210px;white-space:nowrap;overflow:hidden;text-overflow: ellipsis'><span class="label label-primary" title="{{ $key->atribut }}">{{ $key->atribut }}</span></td>
                                            </tr>
                                            <tr>
                                                <td class="tdpulangawal" style='padding-bottom:5px;'>{{ trans('all.pulangawal').' '.$key->pulangawal.' '.trans('all.kali').' ('.\App\Utils::sec2pretty($key->pulangawallama).')' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="tdpulangawal" style="padding-bottom:3px;" colspan="2">
                                                    <i style="cursor: pointer;font-size: 1.2em;" title="{{ trans('all.profil') }}" class="fa fa-user" onclick="return rincian('profil',{{ $key->idpegawai }})"></i>&nbsp;
                                                    <i style="cursor: pointer;font-size: 1.2em;" title="{{ trans('all.logabsen') }}" class="fa fa-calendar-o" onclick="return rincian('logabsen',{{ $key->idpegawai }})"></i>&nbsp;
                                                    <i style="cursor: pointer;font-size: 1.2em;" title="{{ trans('all.rekapabsen') }}" class="fa fa-calendar" onclick="return rincian('rekapabsen',{{ $key->idpegawai }})"></i>
                                                </td>
                                            </tr>
                                        </table>
                                    @elseif($jenis == 'peringkatlamakerja')
                                        <table width="100%">
                                            <tr>
                                                <td class="tdPeringkat" colspan="2" style='padding-bottom:5px;width:200px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;'><b title='{{ $key->namapegawai }}'>{!! $key->nama !!}</b></td>
                                            </tr>
                                            <tr>
                                                <td class="tdPeringkat" style='height:23px;padding-bottom:5px;width:210px;white-space:nowrap;overflow:hidden;text-overflow: ellipsis'><span class="label label-primary" title="{{ $key->atribut }}">{{ $key->atribut }}</span></td>
                                            </tr>
                                            <tr>
                                                <td class="tdPeringkat" style='padding-bottom:5px;'>{{ trans('all.lamakerja').' ('.\App\Utils::sec2pretty($key->lamakerja).')' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="tdPeringkat" style="padding-bottom:3px;" colspan="2">
                                                    <i style="cursor: pointer;font-size: 1.2em;" title="{{ trans('all.profil') }}" class="fa fa-user" onclick="return rincian('profil',{{ $key->idpegawai }})"></i>&nbsp;
                                                    <i style="cursor: pointer;font-size: 1.2em;" title="{{ trans('all.logabsen') }}" class="fa fa-calendar-o" onclick="return rincian('logabsen',{{ $key->idpegawai }})"></i>&nbsp;
                                                    <i style="cursor: pointer;font-size: 1.2em;" title="{{ trans('all.rekapabsen') }}" class="fa fa-calendar" onclick="return rincian('rekapabsen',{{ $key->idpegawai }})"></i>
                                                </td>
                                            </tr>
                                        </table>
                                    @elseif($jenis == 'peringkatlamalembur')
                                        <table width="100%">
                                            <tr>
                                                <td class="tdPeringkat" colspan="2" style='padding-bottom:5px;width:210px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;'><b title='{{ $key->namapegawai }}'>{!! $key->nama !!}</b></td>
                                            </tr>
                                            <tr>
                                                <td class="tdPeringkat" style='height:23px;padding-bottom:5px;width:210px;white-space:nowrap;overflow:hidden;text-overflow: ellipsis'><span class="label label-primary" title="{{ $key->atribut }}">{{ $key->atribut }}</span></td>
                                            </tr>
                                            <tr>
                                                <td class="tdPeringkat" style='padding-bottom:5px;'>{{ trans('all.lamalembur').' ('.\App\Utils::sec2pretty($key->lamalembur).')' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="tdPeringkat" style="padding-bottom:3px;" colspan="2">
                                                    <i style="cursor: pointer;font-size: 1.2em;" title="{{ trans('all.profil') }}" class="fa fa-user" onclick="return rincian('profil',{{ $key->idpegawai }})"></i>&nbsp;
                                                    <i style="cursor: pointer;font-size: 1.2em;" title="{{ trans('all.logabsen') }}" class="fa fa-calendar-o" onclick="return rincian('logabsen',{{ $key->idpegawai }})"></i>&nbsp;
                                                    <i style="cursor: pointer;font-size: 1.2em;" title="{{ trans('all.rekapabsen') }}" class="fa fa-calendar" onclick="return rincian('rekapabsen',{{ $key->idpegawai }})"></i>
                                                </td>
                                            </tr>
                                        </table>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <center>{{ trans('all.nodata') }}</center><br>
                @endif
                <div class="col-md-12" id="kelompoktombol">
                    <center>
                        @if($totaldata > config('consts.LIMIT_FOTO'))
                            <span class="loadmorebutton" onclick="more('{{ $jenis }}','{{ $key->startfrom }}')"></span>
                        @endif
                    </center>
                </div>
            @endif
        </div>
      </div>
      <div class="col-lg-12" style="padding-bottom:10px">
          <center id="spinner-loadmore" style="display:none;margin-top:-20px;"><br><i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i></center>
      </div>
    </div>
  </div>

  <!-- Modal -->
  <a href="" id="showmodalpegawai" data-toggle="modal" data-target="#modalpegawai" style="display:none"></a>
  <div class="modal modalpegawai fade" id="modalpegawai" role="dialog" tabindex='-1'>
      <div class="modal-dialog modal-md">

          <!-- Modal content-->
          <div class="modal-content">

          </div>
      </div>
  </div>
  <!-- Modal -->
@stop

@push('scripts')
<script>
    window.detailpegawai=(function(idpegawai){
        $("#showmodalpegawai").attr("href", "");
        $("#showmodalpegawai").attr("href", "{{ url('detailpegawai') }}/"+idpegawai);
        $('#showmodalpegawai').trigger('click');
        return false;
    });

    $('body').on('hidden.bs.modal', '.modalpegawai', function () {
        $(this).removeData('bs.modal');
        $("#" + $(this).attr("id") + " .modal-content").empty();
        $("#" + $(this).attr("id") + " .modal-content").append("Loading...");
    });

    function rincian(jenis,idpegawai){
        $("#showmodalpegawai").attr("href", "");
        if(jenis == 'profil'){
            $('.modal-dialog').removeClass('modal-lg').addClass('modal-md');
            $("#showmodalpegawai").attr("href", "{{ url('detailpegawai') }}/"+idpegawai);
        }else if(jenis == 'logabsen'){
            $("#showmodalpegawai").attr("href", "{{ url('logabsen') }}/"+idpegawai+'/o/o');
        }else if(jenis == 'rekapabsen'){
            $('.modal-dialog').removeClass('modal-md').addClass('modal-lg');
            $("#showmodalpegawai").attr("href", "{{ url('rekapabsen') }}/"+idpegawai+'/o/o');
        }
        $('#showmodalpegawai').trigger('click');
        return false;
    }

    function gantiBulan(jenis,idpegawai){
        var yymm = $('#bulanDetailBeranda').val();
        //alert(jenis+'|'+idpegawai+'|'+yymm);

        var url = '';
        if(jenis == 'logabsen'){
            url = '{{ url('logabsen') }}/'+idpegawai+'/o/'+yymm;
        }else if(jenis == 'rekapabsen') {
            url = '{{ url('rekapabsen') }}/' + idpegawai + '/o/' + yymm;
        }

        if(url != '') {
            $('#contentDetailBeranda').html('');
            $('#contentDetailBeranda').html('<div class="col-lg-12"><center id="spinnerDetailBeranda"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i></center></div>');

            $.ajax({
                type: "GET",
                url: url,
                data: '',
                cache: false,
                success: function (html) {
                    $('#contentDetailBeranda').html('');
                    $('#contentDetailBeranda').html(html);
                    console.log(html);
                }
            });
        }
        return false;
    }

    $('body').on('hidden.bs.modal', '.modalpegawai', function () {
        $(this).removeData('bs.modal');
        $("#" + $(this).attr("id") + " .modal-content").empty();
        $("#" + $(this).attr("id") + " .modal-content").append("Loading...");
    });
</script>
@endpush
