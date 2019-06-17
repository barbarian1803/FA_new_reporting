<?php

include_once 'HeaderBase.php';

class Header2 extends HeaderBase {

    function drawHeader() {
        global $dflt_lang, $SysPrefs; // FIXME should be passed as params

        $this->rep->SetLang(@$this->formData['rep_lang'] ? $this->rep->formData['rep_lang'] : $dflt_lang);
        $doctype = $this->rep->formData['doctype'];
        $header2type = true;

        $lang = user_language();
        $this->rep->SetLang(@$this->formData['rep_lang'] ? $this->rep->formData['rep_lang'] : ( $lang ? $lang : $dflt_lang));

        // leave layout files names without path to enable including
        // modified versions from company/x/reporting directory

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



        $this->rep->row = $this->rep->pageHeight - $this->rep->topMargin;

        $upper = $this->rep->row - 2 * $this->rep->lineHeight;
        $lower = $this->rep->bottomMargin + 8 * $this->rep->lineHeight;
        $iline1 = $upper - 7.5 * $this->rep->lineHeight;
        $iline2 = $iline1 - 8 * $this->rep->lineHeight;
        $iline3 = $iline2 - 1.5 * $this->rep->lineHeight;
        $iline4 = $iline3 - 1.5 * $this->rep->lineHeight;
        $iline5 = $iline4 - 3 * $this->rep->lineHeight;
        $iline6 = $iline5 - 1.5 * $this->rep->lineHeight;
        $iline7 = $lower;
        $right = $this->rep->pageWidth - $this->rep->rightMargin;
        $width = ($right - $this->rep->leftMargin) / 5;
        $icol = $this->rep->pageWidth / 2;
        $ccol = $this->rep->cols[0] + 4;
        $c2col = $ccol + 60;
        $ccol2 = $icol / 2;
        $mcol = $icol + 8;
        $mcol2 = $this->rep->pageWidth - $ccol2;
        $cols = count($this->rep->cols);
        $this->rep->SetDrawColor(205, 205, 205);
        $this->rep->Line($iline1, 3);
        $this->rep->SetDrawColor(128, 128, 128);
        $this->rep->Line($iline1);
        $this->rep->rectangle($this->rep->leftMargin, $iline2, $right - $this->rep->leftMargin, $iline2 - $iline3, "F", null, array(222, 231, 236));
        $this->rep->Line($iline2);
        $this->rep->Line($iline3);
        $this->rep->Line($iline4);
        $this->rep->rectangle($this->rep->leftMargin, $iline5, $right - $this->rep->leftMargin, $iline5 - $iline6, "F", null, array(222, 231, 236));
        $this->rep->Line($iline5);
        $this->rep->Line($iline6);
        $this->rep->Line($iline7);
        $this->rep->LineTo($this->rep->leftMargin, $iline2, $this->rep->leftMargin, $iline4);
        $col = $this->rep->leftMargin;
        for ($i = 0; $i < 5; $i++) {
            $this->rep->LineTo($col += $width, $iline2, $col, $iline4);
        }
        $this->rep->LineTo($this->rep->leftMargin, $iline5, $this->rep->leftMargin, $iline7);
        if ($this->rep->GetLanguageArray()['a_meta_dir'] == 'rtl') // avoid line overwrite in rtl language
            $this->rep->LineTo($this->rep->cols[$cols - 2], $iline5, $this->rep->cols[$cols - 2], $iline7);
        else
            $this->rep->LineTo($this->rep->cols[$cols - 2] + 4, $iline5, $this->rep->cols[$cols - 2] + 4, $iline7);
        $this->rep->LineTo($right, $iline5, $right, $iline7);

// Company Logo
        $this->rep->NewLine();
        $logo = company_path() . "/images/" . $this->rep->company['coy_logo'];
        if ($this->rep->company['coy_logo'] != '' && file_exists($logo)) {
            $this->rep->AddImage($logo, $ccol, $this->rep->row, 0, 40);
        } else {
            $this->rep->fontSize += 4;
            $this->rep->Font('bold');
            $this->rep->Text($ccol, $this->rep->company['coy_name'], $icol);
            $this->rep->Font();
            $this->rep->fontSize -= 4;
        }
// Document title
        $this->rep->SetTextColor(190, 190, 190);
        $this->rep->fontSize += 10;
        $this->rep->Font('bold');
        $this->rep->TextWrap($mcol, $this->rep->row, $this->rep->pageWidth - $this->rep->rightMargin - $mcol - 20, $this->rep->title, 'right');
        $this->rep->Font();
        $this->rep->fontSize -= 10;
        $this->rep->NewLine();
        $this->rep->SetTextColor(0, 0, 0);
        $adrline = $this->rep->row;

// Company data
        $this->rep->TextWrapLines($ccol, $icol, $this->rep->company['postal_address']);
        $this->rep->Font('italic');
        if (@$this->rep->company['phone']) {
            $this->rep->Text($ccol, _("Phone"), $c2col);
            $this->rep->Text($c2col, $this->rep->company['phone'], $mcol);
            $this->rep->NewLine();
        }
        if (@$this->rep->company['fax']) {
            $this->rep->Text($ccol, _("Fax"), $c2col);
            $this->rep->Text($c2col, $this->rep->company['fax'], $mcol);
            $this->rep->NewLine();
        }
        if (@$this->rep->company['email']) {
            $this->rep->Text($ccol, _("Email"), $c2col);

            $url = "mailto:" . $this->rep->company['email'];
            $this->rep->SetTextColor(0, 0, 255);
            $this->rep->Text($c2col, $this->rep->company['email'], $mcol);
            $this->rep->SetTextColor(0, 0, 0);
            $this->rep->addLink($url, $c2col, $this->rep->row, $mcol, $this->rep->row + $this->rep->lineHeight);

            $this->rep->NewLine();
        }
        if (@$this->rep->company['gst_no']) {
            $this->rep->Text($ccol, _("Our VAT No."), $c2col);
            $this->rep->Text($c2col, $this->rep->company['gst_no'], $mcol);
            $this->rep->NewLine();
        }
        if (@$this->rep->formData['domicile']) {
            $this->rep->Text($ccol, _("Domicile"), $c2col);
            $this->rep->Text($c2col, $this->rep->company['domicile'], $mcol);
            $this->rep->NewLine();
        }
        $this->rep->Font();
        $this->rep->row = $adrline;
        $this->rep->NewLine(3);
        $this->rep->Text($mcol + 100, _("Date"));
        $this->rep->Text($mcol + 180, sql2date($this->rep->formData['document_date']));

        $this->rep->NewLine();
        $this->rep->Text($mcol + 100, $this->rep->formData['document_name']);
        $this->rep->Text($mcol + 180, $this->rep->formData['document_number']);
        $this->rep->NewLine(2);

        if ($this->rep->pageNumber > 1)
            $this->rep->Text($mcol + 180, _("Page") . ' ' . $this->rep->pageNumber);
        $this->rep->row = $iline1 - $this->rep->lineHeight;
        $this->rep->fontSize -= 4;
        $this->rep->Text($ccol, $Addr1['title'], $icol);
        $this->rep->Text($mcol, $Addr2['title']);
        $this->rep->fontSize += 4;

// address1
        $temp = $this->rep->row = $this->rep->row - $this->rep->lineHeight - 5;
        $this->rep->Text($ccol, $Addr1['name'], $icol);
        $this->rep->NewLine();
        $this->rep->TextWrapLines($ccol, $icol - $ccol, $Addr1['address']);

// address2
        $this->rep->row = $temp;
        $this->rep->Text($mcol, $Addr2['name']);
        $this->rep->NewLine();
        $this->rep->TextWrapLines($mcol, $this->rep->rightMargin - $mcol, $Addr2['address'], 'left', 0, 0, NULL, 1);

// Auxiliary document information
        $col = $this->rep->leftMargin;
        foreach ($aux_info as $info_header => $info_content) {

            $this->rep->row = $iline2 - $this->rep->lineHeight - 1;
            $this->rep->TextWrap($col, $this->rep->row, $width, $info_header, 'C');
            $this->rep->row = $iline3 - $this->rep->lineHeight - 1;
            $this->rep->TextWrap($col, $this->rep->row, $width, $info_content, 'C');
            $col += $width;
        }
// Payment terms
        $this->rep->row -= (2 * $this->rep->lineHeight);
        $this->rep->Font('italic');
        $this->rep->TextWrap($ccol, $this->rep->row, $right - $ccol, $Payment_Terms);
        $this->rep->Font();

// Line headers
        $this->rep->row = $iline5 - $this->rep->lineHeight - 1;
        $this->rep->Font('bold');
        $count = count($this->rep->headers);
        $this->rep->cols[$count] = $right - 3;
        for ($i = 0; $i < $count; $i++)
            $this->rep->TextCol($i, $i + 1, $this->rep->headers[$i], -2);
        $this->rep->Font();

// Footer
        $this->rep->Font('italic');
        $this->rep->row = $iline7 - $this->rep->lineHeight - 6;

        foreach ($Footer as $line => $txt) {
            if (!is_numeric($line)) { // title => link
                $this->rep->fontSize -= 2;
                $this->rep->TextWrap($ccol, $this->rep->row, $right - $ccol, $line, 'C');
                $this->rep->row -= $this->rep->lineHeight;
                $this->rep->SetTextColor(0, 0, 255);
                $this->rep->TextWrap($ccol, $this->rep->row, $right - $ccol, $txt, 'C');
                $this->rep->SetTextColor(0, 0, 0);
                $this->rep->addLink($txt, $ccol, $this->rep->row, $this->rep->pageWidth - $this->rep->rightMargin, $this->rep->row + $this->rep->lineHeight);
                $this->rep->fontSize += 2;
            } else
                $this->rep->TextWrap($ccol, $this->rep->row, $right - $ccol, $txt, 'C');
            $this->rep->row -= $this->rep->lineHeight;
        }

        $this->rep->Font();
        $temp = $iline6 - $this->rep->lineHeight - 2;

        $this->rep->row = $temp;
    }

}
