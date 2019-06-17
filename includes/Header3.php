<?php

include_once 'HeaderBase.php';

class Header3 extends HeaderBase {

    function drawHeader() {
        // Turn off cell padding for the main report header, restoring the current setting later
        $oldcMargin = $this->rep->cMargin;
        $this->rep->SetCellPadding(0);

        // Set some constants which control header item layout
        // only set them once or the PHP interpreter gets angry
        if ($this->rep->pageNumber == 1) {
            define('COMPANY_WIDTH', 150);
            define('LOGO_HEIGHT', 50);
            define('LOGO_Y_POS_ADJ_FACTOR', 0.74);
            define('LABEL_WIDTH', 80);
            define('PAGE_NUM_WIDTH', 60);
            define('TITLE_FONT_SIZE', 14);
            define('HEADER1_FONT_SIZE', 10);
            define('HEADER2_FONT_SIZE', 9);
            define('FOOTER_FONT_SIZE', 10);
            define('FOOTER_MARGIN', 4);
        }
        // Set some variables which control header item layout
        $companyCol = $this->rep->endLine - COMPANY_WIDTH;
        $headerFieldCol = $this->rep->leftMargin + LABEL_WIDTH;
        $pageNumCol = $this->rep->endLine - PAGE_NUM_WIDTH;
        $footerCol = $this->rep->leftMargin + PAGE_NUM_WIDTH;
        $footerRow = $this->rep->bottomMargin - FOOTER_MARGIN;

        $this->rep->row = $this->rep->pageHeight - $this->rep->topMargin;

        // Set the color of dividing lines we'll draw
        $oldDrawColor = $this->rep->GetDrawColor();
        $this->rep->SetDrawColor(128, 128, 128);

        // Tell TCPDF that we want to use its alias system to track the total number of pages
        //$this->rep->AliasNbPages();

        // Footer
        if ($this->rep->footerEnable) {
            $this->rep->Line($footerRow, 1);
            $prevFontSize = $this->rep->fontSize;
            $this->rep->fontSize = FOOTER_FONT_SIZE;
            $this->rep->TextWrap($footerCol, $footerRow - ($this->rep->fontSize + 1), $pageNumCol - $footerCol, $this->rep->footerText, $align = 'center', $border = 0, $fill = 0, $link = NULL, $stretch = 1);
            $this->rep->TextWrap($pageNumCol, $footerRow - ($this->rep->fontSize + 1), PAGE_NUM_WIDTH, _("Page") . ' ' . $this->rep->pageNumber . '/' . $this->rep->getAliasNbPages(), $align = 'right', $border = 0, $fill = 0, $link = NULL, $stretch = 1);
            $this->rep->fontSize = $prevFontSize;
        }

        //
        // Header
        //
		
		// Print gray line across the page
        $this->rep->Line($this->rep->row + 8, 1);

        $this->rep->NewLine();

        // Print the report title nice and big
        $oldFontSize = $this->rep->fontSize;
        $this->rep->fontSize = TITLE_FONT_SIZE;
        $this->rep->Font('B');
        $this->rep->Text($this->rep->leftMargin, $this->rep->title, $companyCol);
        $this->rep->fontSize = HEADER1_FONT_SIZE;

        // Print company logo if present and requested, or else just print company name
        // Build a string specifying the location of the company logo file
        $logo = company_path() . "/images/" . $this->rep->company['coy_logo'];
        if ($this->rep->companyLogoEnable && ($this->rep->company['coy_logo'] != '') && file_exists($logo)) {
            // Width being zero means that the image will be scaled to the specified height
            // keeping its aspect ratio intact.
            if ($this->rep->scaleLogoWidth)
                $this->rep->AddImage($logo, $companyCol, $this->rep->row + 15, COMPANY_WIDTH, 0);
            else
                $this->rep->AddImage($logo, $companyCol, $this->rep->row - (LOGO_HEIGHT * LOGO_Y_POS_ADJ_FACTOR), 0, LOGO_HEIGHT);
        } else
            $this->rep->Text($companyCol, $this->rep->company['coy_name']);

        // Dimension 1 - optional
        // - only print if available and not blank
        if (count($this->rep->params) > 3)
            if ($this->rep->params[3]['from'] != '') {
                $this->rep->NewLine(1, 0, $this->rep->fontSize + 2);
                $str = $this->rep->params[3]['text'] . ':';
                $this->rep->Text($this->rep->leftMargin, $str, $headerFieldCol);
                $str = $this->rep->params[3]['from'];
                $this->rep->Text($headerFieldCol, $str, $companyCol);
            }

        // Dimension 2 - optional
        // - only print if available and not blank
        if (count($this->rep->params) > 4)
            if ($this->rep->params[4]['from'] != '') {
                $this->rep->NewLine(1, 0, $this->rep->fontSize + 2);
                $str = $this->rep->params[4]['text'] . ':';
                $this->rep->Text($this->rep->leftMargin, $str, $headerFieldCol);
                $str = $this->rep->params[4]['from'];
                $this->rep->Text($headerFieldCol, $str, $companyCol);
            }

        // Tags - optional
        // if present, it's an array of tag names
        if (count($this->rep->params) > 5)
            if ($this->rep->params[5]['from'] != '') {
                $this->rep->NewLine(1, 0, $this->rep->fontSize + 2);
                $str = $this->rep->params[5]['text'] . ':';
                $this->rep->Text($this->rep->leftMargin, $str, $headerFieldCol);
                $str = '';
                for ($i = 0; $i < count($this->rep->params[5]['from']); $i++) {
                    if ($i != 0)
                        $str .= ', ';
                    $str .= $this->rep->params[5]['from'][$i];
                }
                $this->rep->Text($headerFieldCol, $str, $companyCol);
            }

        // Report Date - time period covered
        // - can specify a range, or just the end date (and the report contents
        //   should make it obvious what the beginning date is)
        $this->rep->NewLine(1, 0, $this->rep->fontSize + 2);
        $str = _("Report Period") . ':';
        $this->rep->Text($this->rep->leftMargin, $str, $headerFieldCol);
        $str = '';
        if (isset($this->rep->params[1]['from']) && $this->rep->params[1]['from'] != '')
            $str = $this->rep->params[1]['from'] . ' - ';
        $str .= $this->rep->params[1]['to'];
        $this->rep->Text($headerFieldCol, $str, $companyCol);

        // Turn off Bold
        $this->rep->Font();

        $this->rep->NewLine(1, 0, $this->rep->fontSize + 1);

        // Make the remaining report headings a little less important
        $this->rep->fontSize = HEADER2_FONT_SIZE;

        // Timestamp of when this copy of the report was generated
        $str = _("Generated At") . ':';
        $this->rep->Text($this->rep->leftMargin, $str, $headerFieldCol);
        $str = Today() . '   ' . Now();
        if ($this->rep->company['time_zone'])
            $str .= ' ' . date('O') . ' GMT';
        $this->rep->Text($headerFieldCol, $str, $companyCol);

        // Name of the user that generated this copy of the report
        $this->rep->NewLine(1, 0, $this->rep->fontSize + 1);
        $str = _("Generated By") . ':';
        $this->rep->Text($this->rep->leftMargin, $str, $headerFieldCol);
        $str = $this->rep->user;
        $this->rep->Text($headerFieldCol, $str, $companyCol);

        // Display any user-generated comments for this copy of the report
        if ($this->rep->params[0] != '') { // Comments
            $this->rep->NewLine(1, 0, $this->rep->fontSize + 1);
            $str = _("Comments") . ':';
            $this->rep->Text($this->rep->leftMargin, $str, $headerFieldCol);
            $this->rep->Font('B');
            $this->rep->Text($headerFieldCol, $this->rep->params[0], $companyCol, 0, 0, 'left', 0, 0, $link = NULL, 1);
            $this->rep->Font();
        }

        // Add page numbering to header if footer is turned off
        if (!$this->rep->footerEnable) {
            $str = _("Page") . ' ' . $this->rep->pageNumber . '/' . $this->rep->getAliasNbPages();
            $this->rep->Text($pageNumCol, $str, 0, 0, 0, 'right', 0, 0, NULL, 1);
        }

        // Print gray line across the page
        $this->rep->Line($this->rep->row - 5, 1);

        // Restore font size to user-defined size
        $this->rep->fontSize = $oldFontSize;

        // restore user-specified cell padding for column headers
        $this->rep->SetCellPadding($oldcMargin);

        // scoot down the page a bit
        $oldLineHeight = $this->rep->lineHeight;
        $this->rep->lineHeight = $this->rep->fontSize + 1;
        $this->rep->row -= ($this->rep->lineHeight + 6);
        $this->rep->lineHeight = $oldLineHeight;

        // Print the column headers!
        $this->rep->Font('I');
        if ($this->rep->headers2 != null) {
            $count = count($this->rep->headers2);
            for ($i = 0; $i < $count; $i++)
                $this->rep->TextCol2($i, $i + 1, $this->rep->headers2[$i], $corr = 0, $r = 0, $border = 0, $fill = 0, $link = NULL, $stretch = 1);
            $this->rep->NewLine();
        }
        $count = count($this->rep->headers);
        for ($i = 0; $i < $count; $i++)
            $this->rep->TextCol($i, $i + 1, $this->rep->headers[$i], $corr = 0, $r = 0, $border = 0, $fill = 0, $link = NULL, $stretch = 1);
        $this->rep->Font();

        $this->rep->NewLine(2);

        // restore user-specified draw color
        $this->rep->SetDrawColor($oldDrawColor[0], $oldDrawColor[1], $oldDrawColor[2]);
    }

}
