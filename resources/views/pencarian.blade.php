@extends('layouts/master')
@section('title', trans('all.beranda'))
@section('content')
  
  <script>
  $(document).ready(function() {
    @if(Session::get('message'))
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
    @endif
  });

  function goto(page){
    window.location.href=page;
  }
  </script>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{ trans('all.pencarian') }}</h2>
    </div>
    <div class="col-lg-2">

    </div>
  </div>
  
  <div class="wrapper wrapper-content animated fadeIn">
    @if(Session::has('conf_webperusahaan'))
      <div class="row">
        <div class="col-lg-12">
          <div class="ibox float-e-margins">
            <div class="ibox-content">
              <h2>{{trans('all.hasilpencarian') }} : "{{ $pencarian }}"</h2>
              @if(count($hasilpencarians) > 0)
                @foreach($hasilpencarians as $hasilpencarian)
                  <div class="hr-line-dashed"></div>
                  <div class="search-result">
                    <h3><a href="detailpencarian/{{ $hasilpencarian->data }}/{{ $hasilpencarian->id }}">{{ $hasilpencarian->nama }}</a></h3>
                    <span class="search-link">{{ $hasilpencarian->data.' - '.$hasilpencarian->kode }}</span>
                    @if($hasilpencarian->atribut != "")
                      <p>{{ $hasilpencarian->atribut }}</p>
                    @endif
                  </div>
                @endforeach
                <div class="hr-line-dashed"></div>
                <center>{!! $hasilpencarians->links() !!}</center>
              @else
                <div class="hr-line-dashed"></div>
                <div class="search-result">
                  <center><p>{{ trans('all.nodata') }}</p></center>
                </div>
                <div class="hr-line-dashed"></div>
              @endif
            </div>
          </div>
        </div>
      </div>
    @else
      <div class="row">
        <div class="col-lg-12">
          <div class="ibox float-e-margins">
            <div class="ibox-content text-center p-md">
              <h2>
                {{ trans('all.selamatdatang') }}
              </h2>
              @if(Session::get('conf_totalperusahaan') == 0)
                <p>
                  {{ trans('all.andatidakberelasidenganperusahaanmanapun') }}
                </p>
                <p>
                  <a href="{!! url('tambahperusahaanbaru') !!}" class="btn btn-primary">{{ trans('all.tambahkanperusahaan') }}</a>
                </p>
              @else
                <p>{{ trans('all.silahkanpilihperusahaan') }}</p>
              @endif
            </div>
          </div>
        </div>
      </div>
    @endif
  </div>

  <!-- Modal detail-->
  <div class="modal fade" id="myModal" role="dialog" tabindex='-1'>
    <div class="modal-dialog modal-sm">
      
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
          <h4 class="modal-title">{{ trans('all.detail') }}</h4>
        </div>
        <div class="modal-body" style="max-height:460px;overflow: auto;">
        </div>
        <div class="modal-footer">
          <table>
            <tr>
              <td style="padding:0px;">
                <button class="btn btn-primary" data-dismiss="modal"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.tutup') }}</button>
              </td>
            </tr>
          </table>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal tambah detail-->
@stop

