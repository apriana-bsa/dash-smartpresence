<?php
  header('Content-Type: text/javascript; charset=UTF-8');
?>
function alertConfirmCustom(msg,isCloseOnConfirm,confim,cancel,btn_ya,btn_tidak)
{
swal({
        title: "",
        text: msg,
        type: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn-danger",
        confirmButtonText: btn_ya,
        cancelButtonText: btn_tidak,
        allowEscapeKey: true,
        closeOnConfirm: isCloseOnConfirm,
        onClose: cancel
    },
    function(isConfirm) {
        if (isConfirm) {
            confim();
        }
    }
    );
}
function alertConfirm(msg,confim,cancel,btn_ya="Ya",btn_tidak="Tidak")
{
alertConfirmCustom(msg,true,confim,cancel,btn_ya,btn_tidak);
}
function alertConfirmNotClose(msg,confim,cancel,btn_ya='Ya',btn_tidak='Tidak')
{
alertConfirmCustom(msg,false,confim,cancel,btn_ya,btn_tidak);
}
function alertCustom(msg,_type,callback)
{
swal({
title: "",
text: msg,
type: _type,
showCancelButton: false,
confirmButtonColor: "#DD6B55",
confirmButtonText: "Ok",
allowEscapeKey: true,
closeOnConfirm: true,
onClose: callback,
}
);
}

function alertSuccess(msg,callback)
{
alertCustom(msg,"success",callback);
}
function alertInfo(msg,callback)
{
alertCustom(msg,"info",callback);
}
function alertWarning(msg,callback)
{
alertCustom(msg,"warning",callback);
}
function alertError(msg,callback)
{
alertCustom(msg,"error",callback);
}
function isNumeric(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}
function isAngkaValid(angka, batasbawah, batasatas, pakaikoma) //jika return 0 --> benar; 1 --> tidak valid; 2 --> melebihi batas atas; 3 --> melebihi batas bawah; 4 --> tidak boleh koma
{
  if (pakaikoma==0 && angka.indexOf(".")!=-1) {
    return 4;
  }
  else
  if (isNumeric(angka)==false) {
    return 1;
  }
  else
  if (parseFloat(angka)+0>batasatas) {
    return 2;
  }
  else if (parseFloat(angka)+0<batasbawah) {
    return 3;
  }
  return 0;
}
function cekAlertAngkaValid(angka_raw, batasbawah, batasatas, pakaikoma, label, callback)
{
  angka_raw=decodeURIComponent(angka_raw).trim();
  if (angka_raw=="")
  {
    alertWarning(label+' Kosong',callback);
    return false;
  }
  else
  {
    var angka=replaceAll(angka_raw,',','.');
    switch (isAngkaValid(angka,batasbawah,batasatas,pakaikoma))
    {
      case 1:
        alertWarning(label+' "'+angka_raw+'" Tidak Valid',callback);
        return false;
        break;
      case 2:
        alertWarning(label+' "'+angka_raw+'" Melebihi Batas Atas '+batasatas,callback);
        return false;
        break;
      case 3:
        alertWarning(label+' "'+angka_raw+'" Melebihi Batas Bawah '+batasbawah,callback);
        return false;
        break;
      case 4:
        alertWarning(label+' "'+angka_raw+'" Tidak Boleh Desimal',callback);
        return false;
        break;
    }
  }
  return true;
}
function escapeRegExp(str) {
  return str.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
}    
function replaceAll(str, find, replace) {
  return str.replace(new RegExp(escapeRegExp(find), 'g'), replace);
}
function onlyNumber(ndecimal, evt)
{
  if ('ya'=='ya')
  {
    var el=evt.target || evt.srcElement;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    //alert(evt.which+" "+evt.keyCode);
    //console.log("evt.which "+evt.which);
    //console.log("evt.keyCode "+evt.keyCode);
    
    var isKoma=false;
    if ((evt.which == 44 || evt.which == 46)) {
      isKoma=true;
    }
    if (isKoma && ndecimal==0) {
      return false;
    }
    var isSelectAll=(el.selectionStart==0 && (el.selectionEnd == el.value.length));
    if (isKoma && (el.value.length==0 || el.selectionStart==0 || isSelectAll)) {
      return false;
    }
    if (isKoma && el.value.split('.').length>1) {
      return false;
    }
    if (!isKoma && charCode > 31 && (charCode < 48 || charCode > 57) && !(
                                                                evt.which == 0
                                                                /*
                                                                &&
                                                                (
                                                                  evt.keyCode == 46 || //del
                                                                  evt.keyCode == 8 ||  //backspace
                                                                  evt.keyCode == 36 || //home
                                                                  evt.keyCode == 35 || //end
                                                                  evt.keyCode == 33 || //pgup
                                                                  evt.keyCode == 34 || //pgdn
                                                                  evt.keyCode == 37 || //left
                                                                  evt.keyCode == 39 || //right
                                                                  evt.keyCode == 38 || //up
                                                                  evt.keyCode == 40 || //down
                                                                  evt.keyCode == 116   //refresh
                                                                )
                                                                */
                                                                )) 
      return false;
    else {
      //console.log('masuk');
      if ( (charCode >= 48 && charCode <= 57) || isKoma ) {
        if (isSelectAll==false && ndecimal!=0)
        {
          var len = el.value.length;
          var index = el.value.indexOf('.');
          if (index==-1) {
            index = el.value.indexOf(',');
          }
          if (isKoma && el.value.length-el.selectionStart>ndecimal) {
            return false;
          }
          if (index > -1 && isKoma) {
            return false;
          }
          if (index > -1) {
            if (el.selectionStart>index) {
              var CharAfterdot = (len + 1) - index;
              if (CharAfterdot > ndecimal+1) {
                return false;
              }
            }
          }
        }
      }
    }
  }
  return true;  
}
function formatDecimal(el) {  
  if (el.value.indexOf('.')>-1) {
    var idx=el.selectionStart;
    el.value = el.value.replace('.',',');
    el.selectionStart=idx;
    el.selectionEnd=idx;
  }
}
function formatRp(evt) {
  var el=evt.target || evt.srcElement;
  
  var idx=el.selectionStart;
  var myVal = ""; // The number part
  // Splitting the value in parts using a dot as decimal separat+or
  var parts = el.value.toString().replace(/^0+/, '').split(",");
  // Filtering out the trash!
  parts[0] = parts[0].replace(/[^0-9]/g,""); 
  if ( parts[1] ) { myDec = ","+parts[1] }
  // Adding the thousand separator
  while ( parts[0].length > 3 ) {
      myVal = "."+parts[0].substr(parts[0].length-3, parts[0].length )+myVal;
      parts[0] = parts[0].substr(0, parts[0].length-3)
  }
  if (el.value != parts[0]+myVal) {
    var d=0;
    if (el.value.length>(parts[0]+myVal).length)
    {
      d=-1;
    }
    else
    if (el.value.length<(parts[0]+myVal).length)
    {
      d=1;
    }
    el.value = parts[0]+myVal;
    
    if (evt.keyCode==8 && el.value.substr(idx,1)=='.') {
      idx=(idx>0 ? idx-1 : idx);
    }
    el.selectionStart=idx+d;
    el.selectionEnd=idx+d;
  }
}
function aktifkanTombol() {
  if ($('#login').length)
  { 
    $('#login').removeAttr('data-loading');
    $('#login').removeAttr('disabled');
  }
  if ($('#proses').length)
  { 
    $('#proses').removeAttr('data-loading');
    $('#proses').removeAttr('disabled');
  }
  if ($('#penambahan').length)
  { 
    $('#penambahan').removeAttr('data-loading');
    $('#penambahan').removeAttr('disabled');
  }
  if ($('#pengurangan').length)
  { 
    $('#pengurangan').removeAttr('data-loading');
    $('#pengurangan').removeAttr('disabled');
  }    
  if ($('#submit').length)
  { 
    $('#submit').removeAttr('data-loading');
    $('#submit').removeAttr('disabled');
  }  
  if ($('#submit2').length)
  {
    $('#submit2').removeAttr('data-loading');
    $('#submit2').removeAttr('disabled');
  }
  if ($('#hapusnilai').length)
  {
    $('#hapusnilai').removeAttr('data-loading');
    $('#hapusnilai').removeAttr('disabled');
  }
  if ($('#simpan').length)
  {
    $('#simpan').removeAttr('data-loading');
    $('#simpan').removeAttr('disabled');
  }  
  if ($('.simpan').length)
  {
    $('.simpan').removeAttr('data-loading');
    $('.simpan').removeAttr('disabled');
  }
  if ($('#simpandanulangi').length)
  {
    $('#simpandanulangi').removeAttr('data-loading');
    $('#simpandanulangi').removeAttr('disabled');
  }    
  if ($('#batal').length)
  {
    $('#batal').removeAttr('data-loading');
    $('#batal').removeAttr('disabled');
  }      
  if ($('.batal').length)
  {
    $('.batal').removeAttr('data-loading');
    $('.batal').removeAttr('disabled');
  }      
  if ($('#back').length)
  {
    $('#back').removeAttr('data-loading');
    $('#back').removeAttr('disabled');
  }
  if ($('.back').length)
  {
    $('.back').removeAttr('data-loading');
    $('.back').removeAttr('disabled');
  }
  if ($('#kembali').length)
  {
    $('#kembali').removeAttr('data-loading');
    $('#kembali').removeAttr('disabled');
  }  
  if ($('#clear').length)
  {
    $('#clear').removeAttr('data-loading');
    $('#clear').removeAttr('disabled');
  }
  if ($('#tutupmodal').length)
  {
    $('#tutupmodal').removeAttr('data-loading');
    $('#tutupmodal').removeAttr('disabled');
  }
  if ($('#reset').length)
  {
    $('#reset').removeAttr('data-loading');
    $('#reset').removeAttr('disabled');
  }
}

function setFocus(el) {
  setTimeout(function(){el.focus();},200);
  
}

function checkboxclick(param,disabled,input,input2){
  if($("#"+param).prop('checked')){
    $("#"+param).prop('checked', true);
    if(disabled == false){
      $('.'+input).css('display','');
      $('.'+input2).css('display','');
    }else{
      $('#'+input).removeAttr('disabled');
      $('#'+input2).removeAttr('disabled');
    }
  }else{
    $("#"+param).prop('checked', false);
    if(disabled == false){
      $('.'+input).css('display', 'none');
      $('.'+input2).css('display', 'disabled');
    }else{
      $('#'+input).attr('disabled', 'disabled');
      $('#'+input2).attr('disabled', 'disabled');
    }
  }
}

function spanclick(param,disabled,input,input2){
  if($("#"+param).prop('checked')){
    $("#"+param).prop('checked', false);
    if(disabled == false){
      $('.'+input).css('display', 'none');
      $('.'+input2).css('display', 'none');
    }else{
      $('#'+input).attr('disabled', 'disabled');
      $('#'+input2).attr('disabled', 'disabled');
    }
  }else{
    $("#"+param).prop('checked', true);
    if(disabled == false){
      $('.'+input).css('display','');
      $('.'+input2).css('display','');
    }else{
      $('#'+input).removeAttr('disabled');
      $('#'+input2).removeAttr('disabled');
    }
  }
}

function spanClick(param){
  document.getElementById(param).checked = !document.getElementById(param).checked;
  el = document.getElementById(param);
  ev = document.createEvent('Event');
  ev.initEvent('change', true, false);
  el.dispatchEvent(ev);
}

function checkboxallclick(param,target){
  if($("#"+param).prop('checked')){
    if(!$("."+target).prop('disabled')){
        $("#"+param).prop('checked', true);
        $("."+target).prop('checked', true);
    }
  }else{
    $("#"+param).prop('checked', false);
    $("."+target).prop('checked', false);
  } 
}

function spanallclick(param,target){
  if($("#"+param).prop('checked')){
    $("#"+param).prop('checked', false);
    $("."+target).prop('checked', false);
  }else{
    if(!$("."+target).prop('disabled')){
        $("#"+param).prop('checked', true);
        $("."+target).prop('checked', true);
    }
  } 
}

function checkAllAttr(_child, _parent){
  var totalChecked = 0;
  $.each($("."+_child), function( index, value){
    if (value.checked) {
      totalChecked++;
    }
  });
  if (totalChecked == $("."+_child).length) {
    $("#"+_parent).prop('checked', true);
  } 
  else {
    $("#"+_parent).prop('checked', false);
  }
}

function give(i,param){
  insertAtCaret(param,i);
}

function insertAtCaret(areaId,text) { var txtarea = document.getElementById(areaId); var scrollPos = txtarea.scrollTop; var strPos = 0; var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ? "ff" : (document.selection ? "ie" : false ) ); if (br == "ie") { txtarea.focus(); var range = document.selection.createRange(); range.moveStart ('character', -txtarea.value.length); strPos = range.text.length; } else if (br == "ff") strPos = txtarea.selectionStart; var front = (txtarea.value).substring(0,strPos); var back = (txtarea.value).substring(strPos,txtarea.value.length); txtarea.value=front+text+back; strPos = strPos + text.length; if (br == "ie") { txtarea.focus(); var range = document.selection.createRange(); range.moveStart ('character', -txtarea.value.length); range.moveStart ('character', strPos); range.moveEnd ('character', 0); range.select(); } else if (br == "ff") { txtarea.selectionStart = strPos; txtarea.selectionEnd = strPos; txtarea.focus(); } txtarea.scrollTop = scrollPos; }

/*
function onerrorimg(obj)
{
  obj.src="../back-end/foto/no-image.jpg";
}

function onerrorimg2(obj)
{
  obj.src="../back-end/foto/no-logo.jpg";
} */

function getcolor(param)
{
  var color = ["#1ABC9C", "#2ECC71", "#3498DB", "#9B59B6", "#34495E", "#F1C40F", "#E67E22", "#E74C3C", "#ECF0F1", "#95A5A6",
               "#16A085", "#27AE60", "#2980B9", "#8E44AD", "#2C3E50", "#F39C12", "#D35400", "#C0392B", "#BDC3C7", "#7F8C8D"
              ];
  var total=0;
  for (var i=0;i<param.length;i++) {
    total=total+param.charCodeAt(i);
  }
  return color[total % color.length];
}

function submitform_quicksearch(){
  
  var q = $('#q').val();
  
  if(q == '') {
    return false;
  }
  
  document.getElementById("formpencari").submit();
  return false;
}

function callIframe(element, url) {
  $(element).contents().find('html').html("");
  $(element).attr('src', url);
  $(element).load(function() {
    $(element).get(0).contentWindow.print();
  });
}

function formatNumber2 (num) {
  return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.")
}

$(function(){
  $('#resetfilterpegawai').click(function(){
    $('#nip').val('');
    $('#alamat').val('');
    $('#kewarganegaraan').val('');
    $('#jeniskelamin').val('');
    $('#agama').val('');
    $('#statusperkawinan').val('');
    $('#statuspegawai').val('');
    $('#mengajar').val('');
    $('#jabatan').val('');
    $('#jamkerja').val('');
    $("#status").val("");
    return false;
  });

  $('#resetfiltersiswa').click(function(){
    $('#nis').val('');
    $('#kelas').val('');
    $('#jeniskelamin').val('');
    $('#agama').val('');
    $('#status').val('');
    return false;
  });
});

function submithapus(id){
  alertConfirm("Apakah yakin akan menghapus data ini ?",
    function(){
      document.getElementById(id).click();
    }
  );
}

function ke(url){
  window.location.href=url;
}

function isEmail(email) {
  var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  return regex.test(email);
}

function hoverFoto(param,id){
  if(param == 'ya'){
    $('#'+id).css('display', '');
  }else{
    $('#'+id).css('display', 'none');
  }
}

function underConstruction(pesan){
  alertWarning(pesan);
}

function freezeButton(){
    $('#submit').attr('data-loading', '');
    $('#submit').attr('disabled', 'disabled');
    $('#kembali').attr('disabled', 'disabled');
}