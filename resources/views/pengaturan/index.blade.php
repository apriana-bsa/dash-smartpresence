@extends('layouts.master')
@section('title', trans('all.strukturatribut'))
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
  <link href="{{ asset('lib/css')  }}/jquery.orgchart.css" media="all" rel="stylesheet" type="text/css" />
  <style type="text/css">
      #orgChart{
          width: auto;
          height: auto;
      }

      #orgChartContainer{
          width: 100%;
          height: 500px;
          overflow: auto;
          background: #fff;
      }

  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.strukturatribut') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.pengaturan') }}</li>
        <li class="active"><strong>{{ trans('all.strukturatribut') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <div class="ibox float-e-margins">
          
          <div class="ibox-content">
              <div id="orgChartContainer">
                  <div id="orgChart"></div>
              </div>
              <div id="consoleOutput"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script type="text/javascript" src="{{ asset('lib/js') }}/jquery.orgchart.js"></script>
  <script type="text/javascript">
      var testData = [
          {id: 1, name: 'My Organization', parent: 0},
          {id: 2, name: 'CEO Office', parent: 1},
          {id: 3, name: 'Division 1', parent: 1},
          {id: 4, name: 'Division 2', parent: 1},
          {id: 6, name: 'Division 3', parent: 1},
          {id: 7, name: 'Division 4', parent: 1},
          {id: 8, name: 'Division 5', parent: 1},
          {id: 5, name: 'Sub Division', parent: 3},

      ];
      $(function(){
          var org_chart = $('#orgChart').orgChart({
              data: testData,
              showControls: true,
              allowEdit: true,
              newNodeText: 'Tambah',
              onAddNode: function(node){
                  console.log('Created new node on node '+node.data.id);
                  log('Created new node on node '+node.data.id);
                  org_chart.newNode(node.data.id);
              },
              onDeleteNode: function(node){
                  console.log('Deleted node '+node.data.id);
                  log('Deleted node '+node.data.id);
                  org_chart.deleteNode(node.data.id);
              },
              onClickNode: function(node){
                  console.log('Clicked node '+node.data.id);
                  log('Clicked node '+node.data.id);
                  {{--setTimeout(function(){--}}
                      {{--$(".org-input").tokenInput("{{ url('tokenatribut') }}", {--}}
                          {{--theme: "facebook",--}}
                          {{--tokenLimit: 1--}}
                      {{--});--}}
                  {{--},500);--}}
                  console.log(node.data);
              }
          });
      });

      // just for example purpose
      function log(text){
          $('#consoleOutput').append('<p>'+text+'</p>')
      }
  </script>
@stop