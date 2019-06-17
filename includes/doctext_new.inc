<?php

/* * ********************************************************************
  Copyright (C) FrontAccounting, LLC.
  Released under the terms of the GNU General Public License, GPL,
  as published by the Free Software Foundation, either version 3
  of the License, or (at your option) any later version.
  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
  See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
 * ********************************************************************* */
/* * ***
 *
 * 	Set document type dependent elements of common page layout.
 *
 */
global $SysPrefs;

$Addr1 = array(
    'title' => _("Charge To"),
    'name' => @$this->rep->formData['br_name'] ? $this->rep->formData['br_name'] : @$this->rep->formData['DebtorName'],
    'address' => @$this->rep->formData['br_address'] ? $this->rep->formData['br_address'] : @$this->rep->formData['address']
);
$Addr2 = array(
    'title' => _("Delivered To"),
    'name' => @$this->rep->formData['deliver_to'],
    'address' => @$this->rep->formData['delivery_address']
);

// default item column headers
$this->rep->headers = array(_("Item Code"), _("Item Description"), _("Quantity"),
    _("Unit"), _("Price"), _("Discount %"), _("Total"));

// for links use 'text' => 'url'
$Footer[0] = _("All amounts stated in") . " - " . @$this->rep->formData['curr_code'];

if (!in_array($this->rep->formData['doctype'], array(ST_STATEMENT, ST_WORKORDER))) {
    $row = get_payment_terms($this->rep->formData['payment_terms']);
    $Payment_Terms = _("Payment Terms") . ': ' . $row["terms"];
    if ($this->rep->formData['doctype'] == ST_SALESINVOICE && $this->rep->formData['prepaid'])
        $this->rep->formData['prepaid'] = ($row['days_before_due'] >= 0) ? 'final' : 'partial';
}


switch ($this->rep->formData['doctype']) {
    case ST_SALESQUOTE:
        $this->rep->title = _("SALES QUOTATION");
        $this->rep->formData['document_name'] = _("Quotation No.");
        $this->rep->formData['document_date'] = $this->rep->formData['ord_date'];
        $this->rep->formData['document_number'] = $SysPrefs->print_invoice_no() == 0 && isset($this->rep->formData['reference']) ? $this->rep->formData['reference'] : $this->rep->formData['order_no'];
        $aux_info = array(
            _("Customer's Reference") => $this->rep->formData["customer_ref"],
            _("Sales Person") => get_salesman_name($this->rep->formData['salesman']),
            _("Your VAT no.") => $this->rep->formData['tax_id'],
            _("Our Quotation No") => $this->rep->formData['order_no'],
            _("Valid until") => sql2date($this->rep->formData['delivery_date']),
        );
        break;

    case ST_SALESORDER:
        $this->rep->title = ($this->rep->params['print_quote'] ? _("QUOTE") : ($this->rep->formData['prepaid'] ? _("PREPAYMENT ORDER") : _("SALES ORDER")));
        $this->rep->formData['document_name'] = _("Order No.");
        $this->rep->formData['document_date'] = $this->rep->formData['ord_date'];
        $this->rep->formData['document_number'] = $SysPrefs->print_invoice_no() == 0 && isset($this->rep->formData['reference']) ? $this->rep->formData['reference'] : $this->rep->formData['order_no'];
        $this->rep->formData['document_amount'] = $this->rep->formData['order_no'];

        $aux_info = array(
            _("Customer's Reference") => $this->rep->formData["customer_ref"],
            _("Sales Person") => get_salesman_name($this->rep->formData['salesman']),
            _("Your VAT no.") => $this->rep->formData['tax_id'],
            _("Our Order No") => $this->rep->formData['order_no'],
            _("Delivery Date") => sql2date($this->rep->formData['delivery_date']),
        );
        break;

    case ST_CUSTDELIVERY:
        $this->rep->title = ($this->rep->params['packing_slip'] ? _("PACKING SLIP") : _("DELIVERY NOTE"));
        $this->rep->formData['document_name'] = _("Delivery Note No.");
        if (@$packing_slip)
            $Payment_Terms = '';
        $ref = $this->rep->formData['order_'];
        if ($SysPrefs->print_invoice_no() == 0) {
            $ref = get_reference(ST_SALESORDER, $this->rep->formData['order_']);
            if (!$ref)
                $ref = $this->rep->formData['order_'];
        }
        $aux_info = array(
            _("Customer's Reference") => $this->rep->formData["customer_ref"],
            _("Sales Person") => get_salesman_name($this->rep->formData['salesman']),
            _("Your VAT no.") => $this->rep->formData['tax_id'],
            _("Our Order No") => $ref,
            _("To Be Invoiced Before") => sql2date($this->rep->formData['due_date']),
        );
        break;

    case ST_CUSTCREDIT:
        $this->rep->title = _("CREDIT NOTE");
        $this->rep->formData['document_name'] = _("Credit No.");
        $Footer[0] = _("Please quote Credit no. when paying. All amounts stated in") . " - " . $this->rep->formData['curr_code'];

        $aux_info = array(
            _("Customer's Reference") => @$this->rep->formData["customer_ref"],
            _("Sales Person") => get_salesman_name($this->rep->formData['salesman']),
            _("Your VAT no.") => $this->rep->formData['tax_id'],
            _("Our Order No") => $this->rep->formData['order_'],
            _("Due Date") => '',
        );
        break;

    case ST_SALESINVOICE:
        $this->rep->title = $this->rep->formData['prepaid'] == 'partial' ? _("PREPAYMENT INVOICE") : ($this->rep->formData['prepaid'] == 'final' ? _("FINAL INVOICE") : _("INVOICE"));
        $this->rep->formData['document_name'] = _("Invoice No.");
        $this->rep->formData['domicile'] = $this->rep->company['domicile'];
        $Footer[0] = _("Please quote Invoice no. when paying. All amounts stated in") . " - " . $this->rep->formData['curr_code'];

        $deliveries = get_sales_parent_numbers(ST_SALESINVOICE, $this->rep->formData['trans_no']);
        if ($SysPrefs->print_invoice_no() == 0) {
            foreach ($deliveries as $n => $delivery) {
                $deliveries[$n] = get_reference(ST_CUSTDELIVERY, $delivery);
            }
        }
        $aux_info = array(
            _("Customer's Reference") => $this->rep->formData["customer_ref"],
            _("Sales Person") => get_salesman_name($this->rep->formData['salesman']),
            _("Your VAT no.") => $this->rep->formData['tax_id'],
        );
        if ($this->rep->formData['prepaid'] == 'partial') {
            $aux_info[_("Date of Payment")] = sql2date(get_oldest_payment_date($this->rep->formData['trans_no']));
            $aux_info[_("Our Order No")] = $this->rep->formData['order_'];
        } else {
            if ($this->rep->formData['prepaid'] == 'final')
                $aux_info[_("Invoice Date")] = sql2date($this->rep->formData['tran_date']);
            else
                $aux_info[_("Date of Sale")] = sql2date(get_oldest_delivery_date($this->rep->formData['trans_no']));
            $aux_info[_("Due Date")] = sql2date($this->rep->formData['due_date']);
        }
        break;

    case ST_SUPPAYMENT:
        global $systypes_array;

        $this->rep->title = _("REMITTANCE");
        $this->rep->formData['document_name'] = _("Remittance No.");
        $Addr1['title'] = _("Order To");
        $Addr1['name'] = $this->rep->formData['supp_name'];
        $Addr1['address'] = $this->rep->formData['address'];
        $Addr2['title'] = _("Charge To");
        $Addr2['name'] = '';
        $Addr2['address'] = '';

        $aux_info = array(
            _("Customer's Reference") => $this->rep->formData['supp_account_no'],
            _("Type") => $systypes_array[$this->rep->formData["type"]],
            _("Your VAT no.") => $this->rep->formData['tax_id'],
            _("Supplier's Reference") => '',
            _("Due Date") => sql2date($this->rep->formData['tran_date']),
        );
        $this->rep->headers = array(_("Trans Type"), _("#"), _("Date"), _("Due Date"), _("Total Amount"), _("Left to Allocate"), _("This Allocation"));
        break;

    case ST_PURCHORDER:
        $this->rep->title = _("PURCHASE ORDER");
        $this->rep->formData['document_name'] = _("Purchase Order No.");
        $Addr1['title'] = _("Order To");
        $Addr1['name'] = $this->rep->formData['supp_name'];
        $Addr1['address'] = $this->rep->formData['address'];
        $Addr2['title'] = _("Deliver To");
        $Addr2['name'] = $this->rep->company['coy_name'];
        //$Addr2['address'] = $this->rep->company['postal_address']; No, don't destroy delivery address!
        $this->rep->formData['document_date'] = $this->rep->formData['ord_date'];
        $this->rep->formData['document_number'] = $SysPrefs->print_invoice_no() == 0 && isset($this->rep->formData['reference']) ? $this->rep->formData['reference'] : $this->rep->formData['order_no'];

        $aux_info = array(
            _("Customer's Reference") => $this->rep->formData['supp_account_no'],
            _("Sales Person") => $this->rep->formData['contact'],
            _("Your VAT no.") => $this->rep->formData['tax_id'],
            _("Supplier's Reference") => @$this->rep->formData['requisition_no'],
            _("Order Date") => sql2date($this->rep->formData['document_date']),
        );

        $this->rep->headers = array(_("Item Code"), _("Item Description"),
            _("Delivery Date"), _("Quantity"), _("Unit"), _("Price"), _("Total"));
        break;

    case ST_CUSTPAYMENT:
        global $systypes_array;

        $this->rep->title = _("RECEIPT");
        $this->rep->formData['document_name'] = _("Receipt No.");
        $Addr1['title'] = _("With thanks from");
        if ($this->rep->formData['order_'] == "0")
            $this->rep->formData['order_'] = "";
        $aux_info = array(
            _("Customer's Reference") => $this->rep->formData["debtor_ref"],
            _("Type") => $systypes_array[$this->rep->formData["type"]],
            _("Your VAT no.") => $this->rep->formData['tax_id'],
            _("Our Order No") => $this->rep->formData['order_'],
            _("Due Date") => sql2date($this->rep->formData['tran_date']),
        );
        $this->rep->headers = array(_("Trans Type"), _("#"), _("Date"), _("Due Date"), _("Total Amount"), _("Left to Allocate"), _("This Allocation"));
        break;

    case ST_WORKORDER:
        global $wo_types_array;

        $this->rep->title = _("WORK ORDER");
        $this->rep->formData['document_name'] = _("Work Order No.");
        $this->rep->formData['document_date'] = $this->rep->formData['date_'];
        $this->rep->formData['document_number'] = $this->rep->formData['id'];
        $Addr1['name'] = $this->rep->formData['location_name'];
        $Addr1['address'] = $this->rep->formData['delivery_address'];
        $aux_info = array(
            _("Reference") => $this->rep->formData['wo_ref'],
            _("Type") => $wo_types_array[$this->rep->formData["type"]],
            _("Manufactured Item") => $this->rep->formData["StockItemName"],
            _("Into Location") => $this->rep->formData["location_name"],
            _("Quantity") => $this->rep->formData["units_issued"],
        );
        $Payment_Terms = _("Required By") . ": " . sql2date($this->rep->formData["required_by"]);
        $this->rep->headers = array(_("Item Code"), _("Item Description"),
            _("From Location"), _("Work Centre"), _("Unit Quantity"), _("Total Quantity"), _("Units Issued"));
        unset($Footer[0]);
        break;


    case ST_STATEMENT:
        $this->rep->formData['document_name'] = '';
        $this->rep->formData['domicile'] = $this->rep->company['domicile'];
        $Payment_Terms = '';
        $this->rep->title = _("STATEMENT");
        $aux_info = array(
            _("Customer's Reference") => '',
            _("Sales Person") => '',
            _("Your VAT no.") => $this->rep->formData['tax_id'],
            _("Our Order No") => '',
            _("Delivery Date") => '',
        );
        $this->rep->headers = array(_("Trans Type"), _("#"), _("Date"), _("DueDate"), _("Charges"),
            _("Credits"), _("Allocated"), _("Outstanding"));
}

// default values
if (!isset($this->rep->formData['document_date']))
    $this->rep->formData['document_date'] = $this->rep->formData['tran_date'];

if (!isset($this->rep->formData['document_number']))
    $this->rep->formData['document_number'] = $SysPrefs->print_invoice_no() == 0 && isset($this->rep->formData['reference']) ? $this->rep->formData['reference'] : @$this->rep->formData['trans_no'];

// footer generic content
if (@$this->rep->formData['bank_name'])
    $Footer[] = _("Bank") . ": " . $this->rep->formData['bank_name'] . ", " . _("Bank Account") . ": " . $this->rep->formData['bank_account_number'];

if (@$this->rep->formData['payment_service']) { //payment link
    $amt = number_format($this->rep->formData["ov_freight"] + $this->rep->formData["ov_gst"] + $this->rep->formData["ov_amount"], user_price_dec());
    $service = $this->rep->formData['payment_service'];
    $url = payment_link($service, array(
        'company_email' => $this->rep->company['email'],
        'amount' => $amt,
        'currency' => $this->rep->formData['curr_code'],
        'comment' => $this->rep->title . " " . $this->rep->formData['reference']
    ));
    $Footer[_("You can pay through") . " $service: "] = "$url";
}

if ($this->rep->formData['doctype'] == ST_CUSTPAYMENT)
    $Footer[] = _("* Subject to Realisation of the Cheque.");

if ($this->rep->params['comments'] != '')
    $Footer[] = $this->rep->params['comments'];

if (($this->rep->formData['doctype'] == ST_SALESINVOICE || $this->rep->formData['doctype'] == ST_STATEMENT) && $this->rep->company['legal_text'] != "") {
    foreach (explode("\n", $this->rep->company['legal_text']) as $line)
        $Footer[] = $line;
}

$this->rep->formData['recipient_name'] = $Addr1['name'];
