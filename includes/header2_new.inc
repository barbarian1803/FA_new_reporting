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

/**
  Document blueprint use following parameters set in doctext.inc:

  $Addr1,	$Addr2 - address info
  $Payment_Terms - payment terms line
  $Footer - footer texts
  $this->rep->company - company info
  $this->rep->title - report title
  $this->rep->formData - some other info
 * * */

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
