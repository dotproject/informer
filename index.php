<?php /* informer $Id: index.php 2007/04/11 14:07 weboholic */

// check permissions
$denyRead = getDenyRead($m);
$denyEdit = getDenyEdit($m);

if( $denyRead ) $AppUI->redirect( 'm=public&a=access_denied' );

$INFORMER_CONFIG = array();
require_once( 'informer.config.php' );

// setup the title block
$titleBlock = new CTitleBlock( 'Informer','iCandy_Regional_Settings.png',$m,"$m.$a" );
$titleBlock->show();

if (isset( $_GET['tab'] )) $AppUI->setState('InformerVwTab', $_GET['tab']);
$tab = $AppUI->getState( 'InformerVwTab' ) ? $AppUI->getState( 'InformerVwTab' ) : 0;

$tabBox = new CTabBox( '?m=informer','./modules/informer/',$tab );
$tabBox->add( 'vw_weekly','Weekly ' );
$tabBox->add( 'vw_monthly','Monthly' );
$tabBox->show();
?>