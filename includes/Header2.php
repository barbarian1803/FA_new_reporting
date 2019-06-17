<?php

include_once 'HeaderBase.php';

class Header2 extends HeaderBase{
    function drawHeader(){
        global $dflt_lang; // FIXME should be passed as params

        $this->rep->SetLang(@$this->formData['rep_lang'] ? $this->rep->formData['rep_lang'] : $dflt_lang);
        $doctype = $this->rep->formData['doctype'];
        $header2type = true;

        $lang = user_language();
        $this->rep->SetLang(@$this->formData['rep_lang'] ? $this->rep->formData['rep_lang'] : ( $lang ? $lang : $dflt_lang));

        // leave layout files names without path to enable including
        // modified versions from company/x/reporting directory
        include("includes/doctext_new.inc");
        include("includes/header2_new.inc");
        $this->rep->row = $temp;
    }
}
