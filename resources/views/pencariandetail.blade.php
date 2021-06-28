@extends('layouts/master')
@section('title', trans('all.beranda'))
@section('content')
  
  <script>
  function kembali(){
    //window.location.href="pencarian";
    window.history.back();
  }

  function ubah(jenis, id){
    if(jenis == "perusahaan"){
      window.location.href="{{ url('datainduk/perusahaan') }}/"+id+"/edit";
    }else{
      window.location.href="{{ url('datainduk/pegawai/pegawai') }}/"+id+"/edit";
    }
  }
  </script>
  <style>
  td{
    padding:5px;
  }
  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{ trans('all.detailpencarian') }}</h2>
    </div>
    <div class="col-lg-2">

    </div>
  </div>
  
  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <div class="ibox float-e-margins">
          <div class="ibox-content p-md">
            @if($data->jenis == "perusahaan")
              <h2>{{ trans('all.perusahaan') }}</h2>
              <table>
                <tr>
                  <td>
                    <a href="{{ url('fotonormal/perusahaan/'.$data->id) }}" title="{{ $data->nama }}" data-gallery="">
                      <img src="{{ url('foto/perusahaan/'.$data->id) }}" width="200px" height="200px" style="border-radius: 50%;">
                    </a>
                  </td>
                  <td valign="top">
                    <table>
                      <tr>
                        <td>{{ trans('all.nama') }}</td>
                        <td>: {{ $data->nama }}</td>
                      </tr>
                      <tr>
                        <td>{{ trans('all.kode') }}</td>
                        <td>: {{ $data->kode }}</td>
                      </tr>
                      <tr>
                        <td>{{ trans('all.status') }}</td>
                        <td>: {{ $data->status }}</td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <button class="btn btn-primary" style="display:none" onclick="ubah('perusahaan',{{ $data->id }})"><i class="fa fa-pencil"></i>&nbsp;&nbsp;{{ trans('all.ubah') }}</button>
                          <button class="btn btn-primary" onclick="kembali()"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            @elseif($data->jenis == "pegawai")
              <h2>{{ trans('all.pegawai') }}</h2>
              <table>
                <tr>
                  <td>
                    <a href="{{ url('fotonormal/pegawai/'.$data->id) }}" title="{{ $data->nama }}" data-gallery="">
                      <img src="{{ url('foto/pegawai/'.$data->id) }}" width="220px" height="220px" style="border-radius: 50%;">
                    </a>
                  </td>
                  <td valign="top">
                    <table>
                      <tr>
                        <td>{{ trans('all.nama') }}</td>
                        <td>: {{ $data->nama }}</td>
                      </tr>
                      <tr>
                        <td>{{ trans('all.pin') }}</td>
                        <td>: {{ $data->pin }}</td>
                      </tr>
                      <tr>
                        <td>{{ trans('all.nomorhp') }}</td>
                        <td>: {{ $data->nomorhp }}</td>
                      </tr>
                      <tr>
                        <td>{{ trans('all.tanggalaktif') }}</td>
                        <td>: {{ $data->tanggalaktif }}</td>
                      </tr>
                      <tr>
                        <td>{{ trans('all.status') }}</td>
                        <td>: {{ $data->status }}</td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <button class="btn btn-primary" style="display:none" onclick="ubah('perusahaan',{{ $data->id }})"><i class="fa fa-pencil"></i>&nbsp;&nbsp;{{ trans('all.ubah') }}</button>
                          <button class="btn btn-primary" onclick="kembali()"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            @endif
          </div>
        </div>
      </div>
    </div>
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

