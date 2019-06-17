# FA new reporting

This is basically just update of library for FA reporting function. No major changes. Now, it uses TCPDF version 6.2.26 and FPDI version 2.2.0. 
The existing FA reporting functionality uses TCPDF version 4.0.027_PHP4 and FPDI version 1.2.1.

Some changes are applied but it is not major changes. Several reports have been checked to be successfully generated but not all checked.
Some differencec in the pdf are seen with margin on the PDF.

If you want to try this, just download this repository and replace the existing reporting folder.

# New functionality header class dependency injection.

Now you can create your own header file and then inject it to the report by extending class HeaderBase (reporting/includes/HeaderBase.php).

Extend this and pass the object via SetHeaderType.

Some example for Header2 function and Header3 function are implemented as class Header2 and class Header3.
To use it, simply change: 

```
$rep->SetHeaderType('Header2');
```

to

```
include_once($path_to_root . "/reporting/includes/Header2.php");
...
...
$rep->SetHeaderType(new Header2($rep));
```
