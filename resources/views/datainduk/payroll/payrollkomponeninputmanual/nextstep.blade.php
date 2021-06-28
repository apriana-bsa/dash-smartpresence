@extends('layouts.master')
@section('title', trans('all.payrollkomponeninputmanual'))
@section('content')
  
  @if(Session::get('message'))
    <script>
    $(document).ready(function() {
      setTimeout(function() {
                  toastr.options = {
                      closeButton: true,
                      progressBar: true,
                      timeOut: 5000,
                      extendedTimeOut: 5000,
                      positionClass: 'toast-bottom-right'
                  };
                  toastr.success('{{ Session::get("message") }}', '{{ trans("all.pemberitahuan") }}');
              }, 500);
    });
    </script>
  @endif
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.payrollkomponeninputmanual') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.payroll') }}</li>
        <li class="active"><strong>{{ trans('all.payrollkomponeninputmanual') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">
    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="ibox float-e-margins">
        <div class="alert alert-danger">
            <center>
                {{ $keterangan }}
            </center>
        </div>
        <button type="button" id="kembali" class="btn btn-primary" onclick="ke('{{ url('datainduk/payroll/payrollkomponeninputmanual') }}')"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>&nbsp;&nbsp;
        <p></p>
        <div class="ibox-content">
          <table width=100% class="table datatable table-striped table-condensed table-hover">
            <thead>
              <tr>
                <td class="opsi2"><b>{{ trans('all.kode') }}</b></td>
                <td class="nama"><b>{{ trans('all.nama') }}</b></td>
                <td class="opsi2"><center><b>{{ trans('all.adadata') }}</b></center></td>
                <td class="opsi4"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
              </tr>
            </thead>
            <tbody>
              @if($datapayrollkomponenmaster != '')
                @foreach($datapayrollkomponenmaster as $key)
                  <tr>
                    <td>{{ $key->kode }}</td>
                    <td>{{ $key->nama }}</td>
                    <td><center>{{ $key->adadata }}</center></td>
                    <td>
                      <center>
                        <a href="{{ url('datainduk/payroll/payrollkomponeninputmanual/'.$key->id.'/ubah') }}">{{ trans('all.ubah') }}</a>&nbsp;&nbsp;
                        <a href="#" onclick="hapusData({{$key->id}})">{{ trans('all.hapus') }}</a>
                      </center></td>
                  </tr>
                @endforeach
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script>
    function hapusData(id){
      alertConfirm('{{trans('all.apakahyakinakanmenghapusdataini')}}',
        function(){
          window.location.href="{{ url('datainduk/payroll/payrollkomponeninputmanual/') }}/"+id+'/hapus';
        }
      );
    }

    $(function() {
        $('.datatable').DataTable({
            bStateSave: true,
            columnDefs: [{
                "targets": 3,
                "orderable": false,
                "searchable": false
            }],
            language: lang_datatable,
            order: [[1, 'asc']]
        });
    });
  </script>
@stop