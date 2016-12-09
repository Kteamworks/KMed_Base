

<?php

include_once("../../globals.php");



// when the Cancel button is pressed, where do we go?
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';

if ($_POST['confirm']) {
    // set the discharge flag of the indicated form
	 	$admit_to_ward=$_POST['admit_to_ward'];
	 sqlStatement("update list_options set is_default=0 where list_id='".$_POST['admit_to_ward']."'
	and option_id='".$_POST['admit_to_bed']."'");
	 if($admit_to_ward=='Private')
  {
	  $pr=sqlStatement("select option_id, concat('SP',substring(option_id,2,instr(option_id,',')-2)) Bed1,concat('SP-',substring(option_id,instr(option_id,',')+1)) Bed2 from list_options where option_id like '".$_POST['admit_to_bed']. "'");
	  $pr1=sqlFetchArray($pr);
	  $bed1=$pr1['Bed1'];
	  $bed2=$pr1['Bed2'];
	  sqlStatement("UPDATE list_options SET is_default=0 WHERE option_id= '".$bed1. "'");
	  sqlStatement("UPDATE list_options SET is_default=0 WHERE option_id= '".$bed2. "'");
	  
	  
  }
     $status='discharge';
	//$today = date("Y-m-d H:i:s"); 
	$today = date('Y-m-d H:i:s', strtotime($_POST['discharge_date']));
	//$today=strtotime($today);
  	$e=$GLOBALS['encounter'];
	$reason=$_POST['reason'];
    sqlStatement("UPDATE t_form_admit SET status=
'discharge',discharge_date='".$today."',reason_for_discharge='".$reason."',discharged_by='".$_SESSION['authUser']."' where admit_to_bed='".$_POST['admit_to_bed']."' and encounter='".$e."'");
    //if ($_POST['id'] != "*" && $_POST['id'] != '')
    // log the event   
    newEvent("delete", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "Form ".$_POST['formname']." deleted from Encounter ".$_POST['encounter']);

    // redirect back to the encounter
    $address = "{$GLOBALS['rootdir']}/patient_file/encounter/$returnurl";
    echo "\n<script language='Javascript'>top.restoreSession();window.location='$address';</script>\n";
    exit;
}
?>
<html>

<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<!-- supporting javascript code -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>

<script type="text/javascript" src="../../../library/dialog.js"></script>
<!-- pop up calendar -->
<style type="text/css">@import url(<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.css);</style>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

</head>

<body class="body_top">

<span class="title">Discharge</span>

<form method="post" action="<?php echo $rootdir;?>/patient_file/encounter/discharge_form.php" name="my_form" id="my_form" >
<?php
// output each GET variable as a hidden form input
foreach ($_GET as $key => $value) {
    echo '<input type="hidden" id="'.$key.'" name="'.$key.'" value="'.$value.'"/>'."\n";
}
?>
<input type="hidden" id="confirm" name="confirm" value="1"/>
 <?php if (is_numeric($pid)) {
    
    $result = getAdmitData($pid, "*");
  }
   $admit_to_ward=($result['admit_to_ward']);
    $admit_to_bed=($result['admit_to_bed']);
	$admit_date=($result['admit_date']);
	
   ?>
 <input type="hidden" id="admit_to_ward" name="admit_to_ward" value='<?php echo attr($admit_to_ward);?>'>
<input type="hidden" id="admit_to_bed" name="admit_to_bed" value='<?php echo attr($admit_to_bed);?>'>
<input type="hidden" id="admit_date" name="admit_date" value='<?php echo attr($admit_date);?>'>
<p>
You are about to discharge a Patient from '<?php echo attr($admit_to_ward);?>':'<?php echo attr($admit_to_bed)?>'
</p>
<table>
<?php
$today = date('Y-m-d H:i:s',strtotime("+0 days"));
?>
<tr>
<td align="left" class="forms"><?php echo xlt('Discharge date'); ?>:</td>
	   	<td class="forms">
		
			   <input type='text' size='10' name='discharge_date' id='discharge_date' <?php echo attr ($disabled)?>;
       value='<?php echo attr($today); ?>' 
       title='<?php echo xla('yyyy-mm-dd Date of Discharge'); ?>'
       onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
        <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
        id='img_end_date' border='0' alt='[?]' style='cursor:pointer;cursor:hand'
        title='<?php echo xla('Click here to choose a date'); ?>'>
		</td>
</tr>
<tr>
 <td><?php echo xlt('Reason For Discharge'); ?>:</td>
 <td><?php
 $query = "SELECT title FROM list_options  WHERE ".
          "list_id ='Reason_For_Discharge' "; 
          $ures = sqlStatement($query);
          echo "   <select name='reason'>\n";
         while ($urow = sqlFetchArray($ures)) {
             $reason= $urow['title'];
             echo "    <option value='" . attr($reason) ."'";
             if ($reason=="Normal") echo " selected";
             echo ">" . text($urow['title'])  . "\n";
             }
            echo "   </select>\n";
     ?>
  </td>
</tr>
</table>
<input type="button" id="confirmbtn" name="confirmbtn" value="Yes, Discharge this Patient">
<input type="button" id="cancel" name="cancel" value="Cancel">
</form>

</body>
<script language="javascript">
/* required for popup calendar */
//Calendar.setup({inputField:"admit_date", ifFormat:"%Y-%m-%d", button:"img_transfer_date"});
Calendar.setup({inputField:"discharge_date", ifFormat:"%Y-%m-%d %H:%M:%S", button:"img_end_date",showsTime:'true'});
</script>

<script language="javascript">
// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $("#confirmbtn").click(function() { 
	var startDate = new Date($('#admit_date').val());
    var endDate = new Date($('#discharge_date').val());
	if (startDate > endDate){
		
		return validate();
		
	}else
	{
	return ConfirmDelete(); 
	}
});
    $("#cancel").click(function() { location.href='<?php echo "$rootdir/patient_file/encounter/$returnurl";?>'; });
});
function validate()
{
	var startDate = new Date($('#admit_date').val());
    var endDate = new Date($('#discharge_date').val());

if (startDate > endDate){
alert("Please Put the Start and the End Date Properly");
}
}
function ConfirmDelete() {
    if (confirm("This action cannot be undone. Are you sure you wish to discharge this Patient?")) {
        top.restoreSession();
        $("#my_form").submit();
        return true;
    }
    return false;
}

</script>

</html>
