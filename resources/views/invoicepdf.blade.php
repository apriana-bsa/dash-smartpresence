<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>Invoice {!! $invoiceNo !!}</title>
    <link rel="stylesheet" href="{{ asset('invoicepdf/style.css') }}" media="all">
    <style>
      #void {
        position: absolute;
        margin: 40% 10%;
      }
    </style>
  </head>
  <body>
    @if ($isVoid)
    <div style="position: absolute">
      <img id="void" src="{{ asset('invoicepdf/void.png') }}" alt="gambar">
    </div>
    @endif

    <header class="clearfix">
      <h1 style="font-family: Arial, Helvetica, sans-serif;" class="name"><strong>PT BIMA SAKTI ALTERRA</strong></h1>
      <div id="company">
        <img width="200px" src="{{ asset('invoicepdf/logo.png') }}" />
      </div>
    </header>
    <main>
      <div id="details" class="clearfix">
        <div id="client">
          <div class="address">Jalan Ahmad Yani No.319, Denpasar</div>
          <div class="address">Telp: 0361 - 413497</div>
          <div class="address">Email: finance@bsa.id</div>
        </div>
        <div id="invoice">
          <table>
            <tbody>
              <tr>
                <td width="40%"
                  style="text-align: left; border: 0px; padding: 5px 10px"><strong>INVOICE #</strong></td>
                <td width="10%"
                  style="text-align: left; border: 0px; padding: 5px 5px 5px 60px">:</td>
                <td width="60%"
                  style="text-align: left; border: 0px; padding: 5px">{!! $invoiceNo !!}</td>
              </tr>
              <tr>
                <td width="40%"
                  style="text-align: left; border: 0px; padding: 5px 10px"><strong>DATE</strong></td>
                <td width="10%"
                  style="text-align: left; border: 0px; padding: 5px 5px 5px 60px">:</td>
                <td width="60%"
                  style="text-align: left; border: 0px; padding: 5px">{!! $invoiceTanggal !!}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <p>CUSTOMER</p>
      <table style="font-size: 13px;">
        <tr>
          <td width="15%"
            style="text-align: left; border-right: 0px; border-bottom: 0px">
            NAME
          </td>
          <td width="50%"
            style="text-align: left; border-right: 0px; border-bottom: 0px; border-left: 0px;">
            {!! $namaCustomer !!}
          </td>

          <td width="5%" style="text-align: left; border-top: 0px; border-bottom: 0px"></td>
          <td width="15%" style="text-align: left; border-right: 0px; border-bottom: 0px; border-left: 0px"></td>
          <td width="15%" style="text-align: left; border-bottom: 0px; border-left: 0px"></td>
        </tr>
        <tr>
          <td
            style="text-align: left; border-top: 0px; border-right: 0px; border-bottom: 0px">
            ADDRESS
          </td>
          <td style="text-align: left; border-bottom: 0px; border-top: 0px; border-left: 0px; border-right: 0px">
            {!! $alamatCustomer !!}
          </td>
          <td style="border-bottom: 0px; border-top: 0px;"></td>
          <td style="text-align: left; border-bottom: 0px; border-top: 0px; border-left: 0px; border-right: 0px">
            DUE DATE
          </td>
          <td style="text-align: left; border-bottom: 0px; border-top: 0px; border-left: 0px">
            {!! $dueDateCustomer !!}
          </td>
        </tr>
        <tr>
          <td style="text-align: left; border-right: 0px; border-top: 0px">
            PHONE
          </td>
          <td style="text-align: left; border-left: 0px; border-right: 0px; border-top: 0px">
            {!! $telephoneCustomer !!}
          </td>
          <td style="border-bottom: 0px; border-top: 0px"></td>
          <td style="border-left: 0px; border-right: 0px; border-top: 0px"></td>
          <td style="border-left: 0px; border-top: 0px"></td>
        </tr>
      </table>


      <table id="detail" style="font-size: 13px;">
        <thead>
          <tr>
            <th width="7%">NO.</th>
            <th width="26%">DESCRIPTION</th>
            <th width="7%">QTY</th>
            <th width="10%">PERIOD</th>
            <th width="16%">UNIT PRICE (Rp.)</th>
            <th width="17%">TAXED</th>
            <th width="17%">AMOUNT (Rp.)</th>
          </tr>
        </thead>
        <tbody>
        <!--  -->

        @foreach($items as $index=>$item)
        <tr>
            <td>{!! $index+1 !!}</td>
      			<td style="text-align: left">{!! $item['productName'] !!}</td>
      			<td>{!! $item['qty'] !!}</td>
      			<td>{!! $item['period'] !!}</td>
      			<td style="text-align: right">{!! $item['unitPrice'] !!}</td>
      			<td>{!! $item['taxed'] !!}</td>
      			<td style="text-align: right">{!! $item['amount'] !!}</td>
        </tr>
        @endforeach

        <!--  -->
        </tbody>
        <tfoot>
          <tr>
            <td style="border-left: 0px; border-bottom: 0px" colspan="5"></td>
            <td style="text-align: left">Subtotal</td>
            <td style="text-align: right">{!! $subTotal !!}</td>
          </tr>
          <tr>
            <td style="border-left: 0px; border-top: 0px; border-bottom: 0px" colspan="5"></td>
            <td style="text-align: left" >
              @if ($isTaxIncluded)
                <span>INCLUDING PPN 10.0%</span>
              @else
                <span>PPN 10.0%</span>
              @endif
            </td >
            <td style="text-align: right">{!! $ppn !!}</td>
          </tr>
          <tr>
            <td style="border-left: 0px; border-top: 0px; border-bottom: 0px" colspan="5"></td>
            <td style="text-align: left" >
                KODE UNIK
            </td >
            <td style="text-align: right">{!! $kodeUnik !!}</td>
          </tr>
          <tr>
            <td style="border-left: 0px; border-top: 0px; border-bottom: 0px" colspan="5"></td>
            <td style="text-align: left" class="text-bold">TOTAL</td>
            <td style="text-align: right; background: #DDDDDD" class="text-bold">{!! $total !!}</td>
          </tr>
          @if ($isTaxIncluded)
          <tr>
            <td style="border-left: 0px; border-top: 0px; border-bottom: 0px" colspan="5"></td>
            <td style="text-align: left">Amount Received</td>
            <td style="text-align: right">{!! $amountReceived !!}</td>
          </tr>
          @endif
          <tr>
            <td style="border-left: 0px; border-right: 0px; border-top: 0px; border-bottom: 0px" colspan="5"></td>
            <td style="text-align: left; border-left: 0px; border-bottom: 0px">Balance Due</td>
            <td style="text-align: right">{!! $balanceDue !!}</td>
          </tr>
        </tfoot>
      </table>

      <div><strong>PAYMENT DETAIL</strong></div>
      <div style="float: left; width:60%;">
        <table style="font-size: 13px;">
          <tr>
            <td
              style="text-align: left; border-bottom: 0px; border-right: 0px; padding-top: 15px; padding-bottom: 5px;"
            >
            BANK NAME:
            </td>
            <td
              style="text-align: left; border-bottom: 0px; border-left: 0px; padding-top: 15px; padding-bottom: 5px;"
            >
              {!! $namaBank !!}
            </td>
          </tr>
          <tr>
            <td
              style="text-align: left; border-bottom: 0px; border-top: 0px; border-right: 0px; padding-top: 5px; padding-bottom: 5px;"
            >
            BANK BRANCH:
            </td>
            <td
              style="text-align: left; border-bottom: 0px; border-top: 0px; border-left: 0px; padding-top: 5px; padding-bottom: 5px;"
            >
              {!! $cabangBank !!}
            </td>
          </tr>
          <tr>
            <td
              style="text-align: left; border-bottom: 0px; border-top: 0px; border-right: 0px; padding-top: 5px; padding-bottom: 5px;"
            >
            BANK ACCOUNT NUMBER:
            </td>
            <td
              style="text-align: left; border-bottom: 0px; border-top: 0px; border-left: 0px; padding-top: 5px; padding-bottom: 5px;"
            >
             {!! $nomorBank !!}
            </td>
          </tr>
          <tr>
            <td
              style="text-align: left; border-top: 0px; border-right: 0px; padding-top: 5px; padding-bottom: 15px;"
            >
            BANK ACCOUNT NAME:
            </td>
            <td
              style="text-align: left; border-top: 0px; border-left: 0px; padding-top: 5px; padding-bottom: 15px;"
            >
              {!! $atasNamaBank !!}
            </td>
          </tr>
        </table>

          <div style="border: 1px solid #333333; padding: 10px">
            <div style="text-align: left; margin-bottom: 10px;">
              AMOUNT IN WORD
            </div>
            <div style="text-align: left; text-transform: uppercase;">
              {!! $terbilang !!}
            </div>
          </div>

      </div>
      <div style="float: right; padding-left: 50px">
        <table border="0">
          <tr>
            <td
              style="border: 0px; border-bottom: 1px solid #555555; height: 700%;"
            >

              <img src="{{ asset('invoicepdf/sign.png') }}" />
            </td>
          </tr>
          <tr>
            <td
              style="border: 0px; text-align: center; padding-top: 1em; padding-bottom: 0;"
            >
            Bimasakti Altera
            </td>
          </tr>
          <tr>
            <td
              style="border: 0px; text-align: center; padding-top: 1em; padding-bottom: 0;"
            >
            Ayu Krismiyati
            </td>
          </tr>
          <tr>
            <td
              style="border: 0px; text-align: center; padding-top: 1em; padding-bottom: 0;"
            >
            Finance Manager
            </td>
          </tr>
        </table>
      </div>
    </main>
  </body>
</html>
