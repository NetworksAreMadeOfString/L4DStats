<?
/*
  The idea is that will allow me to pull Smarty specific security stuff at will
*/

$TEMPLATE->php_handling = SMARTY_PHP_REMOVE; // default: do not allow php tags

$TEMPLATE->security = TRUE; // Pseudo-safe mode

$TEMPLATE->security_settings[.MODIFIER_FUNCS.] = array(.substr.);

$TEMPLATE->trusted_dir = array(); // No trusted directory. Ever.

$TEMPLATE->register_outputfilter("template_postfilter");

?>
