<?php header("Content-Type: text/html");?>
<?php

###########################################################################
# �X���b�h�f����1_PHP�� ���ݒ�
# Ver3.0 PHP8.4
# https://cgi-garage.com/
###########################################################################
$ltime = localtime(time());
$backtime = sprintf("%04d%02d%02d_%02d%02d",$ltime[5]+1900,$ltime[4]+1,$ltime[3],$ltime[2],$ltime[1]);
$chstr = "";
$set = setread("data/set.cgi");

$cryptpass = "";
if(isset($set["pass"])){
	$cryptpass = auth();
}

$mainset = postget('mainset');
$itiran = postget('itiran');

##########################
if($mainset){
	mainset();
} if($itiran){
	itiran();
}
###########################
$print = "<TABLE border=1>\n"
	   . "<TR>\n<TD bgcolor=#cccccc>���@�P�A�����ݒ�</TD>\n"
	   . "<TD><INPUT type=submit name=mainset value=�����ݒ�ց@��></TD>\n"
	   . "<TD align=center rowspan=14><A href=\"javascript:help('admin')\"><B>HELP</B></A></TD>\n</TR>\n"
	   . "<TR><TD bgcolor=#cccccc>���@�Q�A���O�t�@�C���ꗗ</TD>\n"
	   . "<TD><INPUT type=submit name=itiran value='���O�t�@�C���ꗗ�ց@��'></TD>\n</TR>"
	   . "</TABLE>\n";

$script = "<SCRIPT language='JavaScript'>\n<!--\n";
$script .= "function help(str){\n";
$script .= "\tif(str == 'admin'){	window.open('./help/admin.html','','width=400,height=300,scrollbars');}\n";
$script .= "}\n//-->\n</SCRIPT>";

funcprint($script,'�X���b�h�f����1_PHP�Ł@���ݒ�',$print);
################################################
function itiran(){
	global $set,$chstr,$backtime;

	$del = postget('delbtn');
	$sort = postget('sort');
	$sortcategory = postget('sortcategory');
	$sortchange = postget('sortchange');
	$suuti = postget('suuti');
	$dbase = setread2($set['logfilename']);
	$list = array();
	$koumoku = array('���e��','�z�X�g��','���O','�^�C�g��','���[���A�h���X','�z�[���y�[�W�A�h���X','�R�����g');

	if($sort){
		if($sortchange == ""){
			errorprint("���̓G���[","�������~����I�����Ă��������B");
		}
		$final = "";
		$dbase2 = array();
		$count = 0;
		foreach ($dbase as $k){
			$i = explode("\t",$k);
			$p = array();
			if($sortcategory != ""){
				$p[0] = $i[$sortcategory];
			} else{
				$p[0] = preg_replace("/\t/","",$k);
			}
			if(!mb_ereg("[^0-9]",$p[0]) && $suuti == "1" && $p[0] ){
				$p[0] = sprintf("%010d",$p[0]);
			}
			array_splice($i,0,0,$p[0]);
			array_push($dbase2,implode("\t",$i) . "\t" . $count);
			$count++;
		}
		if($sortchange == "up"){
			sort($dbase2);
		} elseif($sortchange == "down"){
			rsort($dbase2);
		}
		$count = "0";
		$leschange = array();
		foreach ($dbase2 as $k){
			$i = explode("\t",$k);
			if(!mb_ereg("[^0-9]",$i[0]) && $suuti == "1" && $i[0] != " " && $i[0]){
				$i[0] += 0;
			}
			$num = array_pop($i);
			$p = array();
			$p = array_splice($i,0,1);
			array_push($leschange,$num."\t".$count);
			$final .= implode("\t",$i) . "\n";
			$count++;
		}
		setchange3($final,$set['logfilename']);
		$dbase = array();
		$dbase = setread2($set['logfilename']);

		$lesdatarray = setread2('les.cgi');
		$lesdat = "";
		foreach ($lesdatarray as $k){
			$lesnum = explode("\t",$k);
			$newnum = "";
			$hit = 0;
			foreach ($leschange as $q){
				$newnum = explode("\t",$q);
				if($lesnum[0] == $newnum[0]){
					$lesnum[0] = $newnum[1];
					$hit++;
					break;
				}
			}
			if($hit){
				$lesdat .= implode("\t",$lesnum) . "\n";
			}
		}
		setchange3($lesdat,'les.cgi');
	}

	if($del){
		$delchk = postarray('delchk');

		$count = 0;
		foreach ($delchk as $i){
			array_splice($dbase,$i - $count,1);
			$count++;
		}
		$newlog = implode("\n",$dbase) . "\n";
		setchange3($newlog,$set['logfilename']);
		$dbase = array();
		$dbase = setread2($set['logfilename']);

		$lesdata = setread2('les.cgi');
		$newles = array();
		$count = 0;
		foreach ($lesdata as $k){
			$i = explode("\t",$k);
			$frag = "0";
			$delcount = 0;
			foreach ($delchk as $p){
				if($p == $i[0]){
					$frag++;
				} elseif($i[0] >= $p){
					$delcount++;
				}

			}
			if($frag == "0"){
				$i[0] -= $delcount;
				array_push($newles,implode("\t",$i));
			}
			$count++;
		}
		$newlesstr = implode("\n",$newles);
		setchange3($newlesstr,'les.cgi');
	}

	$hyouji = postget('hyouji');
	if($hyouji == ""){
		$hyouji = 10;
	} if($hyouji == ""){
		$hyouji = 10;
	}

	$start = postget('start');
	$end = postget('end');
	$category = postget('category');
	$searchstr = postget('searchstr');
	$searchstr2 = $searchstr;
	if($start == ""){
		$start = 1;
		$end = $hyouji;
	}

	$print = "<P>���@���O����</P>���@���ڎw��@<select name=\"category\">\n";
	$frag = 0;
	$count = 0;
	foreach ($koumoku as $i){
		$print .= "<OPTION VALUE=\"$count\"";
		if($category == $count){
			$print .= " selected";
			$frag++;
		}
		$print .= ">$i</OPTION>\n";
		$count++;
	}
	$print .= "<OPTION VALUE=\"\"";
	if($frag == 0){
		$print .= " selected";
	}
	$print .= ">����</OPTION>\n"
			. "</SELECT>\n"
			. "�@����������<input type=text name=searchstr value=\"$searchstr2\"><BR>"
			. "<INPUT TYPE=text name=hyouji value=\"$hyouji\" size=\"4\">������"
			. "<input type=submit name=itiran value=\"�\��\"><BR><BR>\n"
			. "<HR>\n"
			. "<P>���@�f�[�^�̃\�[�g</P>���@���ڎw��@<select name=\"sortcategory\">\n";
	$frag = 0;
	$count = 0;
	foreach ($koumoku as $i){
		$print .= "<OPTION VALUE=\"$count\"";
		if($sortcategory == $count){
			$print .= " selected";
			$frag++;
		}
		$print .= ">$i</OPTION>\n";
		$count++;
	}
	$print .= "<OPTION VALUE=\"\"";
	if($frag == 0){
		$print .= " selected";
	}
	$print .= ">����</OPTION>\n</SELECT>\n�@"
			. "<input type=radio name=sortchange value=\"up\"";
	if($sortchange == "up"){
		$print .= " checked";
	}
	$print .= ">���� "
			. "<INPUT TYPE=radio name=sortchange value=\"down\"";
	if($sortchange == "down"){
		$print .= " checked";
	}
	$print .= ">�~���@"
			. "<input type=checkbox name=suuti value=\"1\"";
	if($suuti == "1"){
		$print .= " checked";
	}
	$print .= ">���l�`���@"
			. "<input type=submit name=sort value=\"�\�[�g����\"><BR><BR>\n"
			. "<HR>\n"
			. "[ <A href=\"javascript:help('itiran2')\"><B>HELP</B></A> ]<BR><BR>\n"
			. "<TABLE border=1>\n";
	$count = 1;
	$hyoujicount = 0;
	$kcount = "0";
	$sobi = array();
	$print .= "<TR><TD bgcolor=#cccccc nowrap align=center>DATA<BR>�ԍ�</TD>\n"
			. "<TD bgcolor=#cccccc nowrap align=center>�폜</TD>\n";
	foreach ($koumoku as $p){
		$print .= "<TD bgcolor=#cccccc nowrap>$p</TD>\n";
		$kcount++;
	}
	$print .= "</TR>\n";
	foreach ($dbase as $i){
		if($i){
			$line = explode("\t",$i);
			if( $searchstr == "" ||
				($category == "" && $searchstr != "" && mb_substr_count($i,$searchstr) >= 1) ||
				($category != "" && $searchstr != "" && mb_substr_count($line[$category],$searchstr) >= 1) ){
				$hyoujicount++;
				$bgc = "";
				if($hyoujicount >= $start && $hyoujicount <= $end){
					if(($hyoujicount % 2) == 0){
						$bgc = "bgcolor='#e0fdfe'";
					}
					$cnum = $count - 1;
					$print .= "<TR><TD align=center $bgc><B>$count</B></TD>\n"
							. "<TD align=center $bgc><INPUT type=checkbox name=\"delchk[]\" value='".$cnum."'></TD>\n";
					$kcount = 0;
					$line[6] = preg_replace("/<!KAIGYOU>/"," ",$line[6]);
					foreach ($line as $p){
						if($kcount < 7){
							$print .= "<TD $bgc>$p</TD>\n";
						}
						$kcount++;
					}
					$print .= "</TR>\n";
				}
			}
			$count++;
		}
	}
	$count--;
	if($hyoujicount == 0){
		$listcount = $kcount + 2;
		$print .= "<TR><TD colspan=$listcount align=center><B>�Y������f�[�^�͑��݂��܂���</B></TD></TR>\n";
	}
	$print .= "<TR><TD></TD>\n"
			. "<TD><A href=\"javascript:help('itiran')\"><B>HELP</B></A></TD>\n"
			. "<TD colspan='$kcount' align=center><INPUT type=submit name=delbtn value='�I�������f�[�^���폜'></TD></TR>\n"
			. "</TABLE>\n"
			. "<input type=hidden name=itiran value=aaa>";
	$endnum = $end;
	if($category != ""){
		$print .= "<B>".$koumoku[$category]."</B> �� ";
	} if($searchstr != ""){
		$print .= "<B>$searchstr2</B> �̕��������������f�[�^ ";
	} if($end > $hyoujicount){
		$endnum = $hyoujicount;
	}
	$print .= "<B>$hyoujicount</B> ���� <B>$start</B> �� �` <B>$endnum</B>���\��<BR><BR>\n";

	$print .= linkstr($hyoujicount,$hyouji,$start,$end,$category,$searchstr);

	$script = "<SCRIPT language='JavaScript'>\n<!--\n";
	$script .= "function help(str){\n";
	$script .= "\tif(str == 'itiran'){	window.open('./help/itiran.html','','width=400,height=200,scrollbars');}\n";
	$script .= "\tif(str == 'itiran2'){	window.open('./help/itiran2.html','','width=400,height=400,scrollbars');}\n";
	$script .= "}\n//-->\n</SCRIPT>";

	funcprint($script,'�X���b�h�f����1_PHP�Ł@���O�t�@�C���ꗗ',$print);
}

function linkstr($hyoujicount,$hyouji,$start,$end,$category,$searchstr) {
	global $set;
	$frag = "0";
	if($set['pattern'] == "1"){
		$mae = "�y ";
		$usiro = " �z";
	} elseif($set['pattern'] == "2"){
		$mae = "";
		$usiro = "";
	} else{
		$mae = "[ ";
		$usiro = " ]";
	}

	$num = floor($hyoujicount / $hyouji);
	if($hyoujicount % $hyouji != 0){
		$num++;
	}
	$nownum = floor($start / $hyouji) + 1;
	$numstr = "";
	$befnum = "";
	$aftnum = "";
	for($i = 1; $i <= $num; $i++){
		$startnum = ($i - 1) * $hyouji + 1;
		$endnum = $i * $hyouji;
		if($endnum > $hyoujicount){
			$endnum = $hyoujicount;
		}
		if($i == $nownum){
			$numstr .= $mae."<B>".$i."</B>".$usiro." ";
			$frag++;
		} elseif($set['pagechange'] == "0" || 
				($frag == "0" && ($nownum - $set['pagechangecount']) <= $i) ||
				($frag != "0" && ($nownum + $set['pagechangecount']) >= $i) ){ 
			$numstr .= $mae."<A href=\"admin.php?itiran=aaa&start=$startnum&end=$endnum&hyouji=$hyouji";
			if($category != ""){
				$numstr .= "&category=$category";
			} if($searchstr != ""){
				$code = strtocode($searchstr);
				$numstr .= "&searchstr=$code";
			}
			$numstr .= "\">$i</A>".$usiro." ";
		}
		if(  $i == ($nownum - 1) && $set['pagechange'] ){
			$befnum = "<A href=\"admin.php?itiran=aaa&start=$startnum&end=$endnum&hyouji=$hyouji";
			if($category != ""){
				$befnum .= "&category=$category";
			} if($searchstr != ""){
				$code = strtocode($searchstr);
				$befnum .= "&searchstr=$code";
			}
			$befnum .= "\"><B>&lt;&lt;�O��".$hyouji."��</B></A>�@";
		} elseif( $i == ($nownum + 1) && $set['pagechange'] ){
			$aftnum = "�@<A href=\"admin.php?itiran=aaa&start=$startnum&end=$endnum&hyouji=$hyouji";
			if($category != ""){
				$aftnum .= "&category=$category";
			} if($searchstr != ""){
				$code = strtocode($searchstr);
				$aftnum .= "&searchstr=$code";
			}
			$aftnum .= "\"><B>����".$hyouji."��&gt;&gt;</B></A>�@";
		}
	}
	$numstr2 = $befnum.$numstr.$aftnum;
	return($numstr2);
}

function mainset(){
	global $set,$chstr,$backtime;

	$settemp = readtemp('./temp/set.html');
	$henkou = postget('henkou');

	if($henkou){
		$password = postget('password');
		$newpass = postget('newpass');
		$temp = postget('temp');
		$logfilename = postget('logfilename');
		$hyouji = postget('hyouji');
		$logcount = postget('logcount');
		$exhost = postget('exhost');
		$exstr = postget('exstr');
		$erchk = postget('erchk');
		$path = postget('path');
		$pagechange = postget('pagechange');
		$pagechangecount = postget('pagechangecount');
		$pattern = postget('pattern');

		$hyouji = mb_convert_kana($hyouji,"n");
		$logcount = mb_convert_kana($logcount,"n");
		$pagechangecount = mb_convert_kana($pagechangecount,"n");
		$exhost = preg_replace("/�@/"," ",$exhost);
		$exstr = preg_replace("/�@/"," ",$exstr);

		$setkey = array('pass','temp','logfilename','hyouji','logcount','exhost','exstr',
						'erchk','path','pagechange','pagechangecount','pattern');

		$newpassword = "";
		if($password || $newpass){
			$newpassword = passchange($password,$newpass);
		} if($newpassword == ""){
			$newpassword = $set['pass'];
		}

		if(mb_ereg("[^0-9]",$hyouji)){
			errorprint('���̓G���[','�y�[�W���Ƃ̕\�������ɕs���ȕ����񂪓��͂���Ă��܂��B');
		} if(mb_ereg("[^0-9]",$logcount)){
			errorprint('���̓G���[','���O�̕ۑ������ɕs���ȕ����񂪓��͂���Ă��܂��B');
		}
		$setvalue = array($newpassword,$temp,$logfilename,$hyouji,$logcount,$exhost,$exstr,
						$erchk,$path,$pagechange,$pagechangecount,$pattern);
		setchange('./data/set.cgi',$setkey,$setvalue);
		$set = array();
		$set = setread("./data/set.cgi");
	}

	$print = "<TABLE border=1>\n"
		   . "<TR>\n<TD bgcolor=#cccccc>���@�p�X���[�h�ύX</TD>\n"
		   . "<TD>���݂̃p�X���[�h�@<INPUT size=20 type=password name=password><BR>"
		   . "�ύX����p�X���[�h<INPUT size=20 type=password name=newpass></TD>\n"
		   . "<TD rowspan=11><A href=\"javascript:help('mainset')\"><B>HELP</B></A></TD></TR>\n"
		   . "<TR><TD bgcolor=#cccccc>���@�e���v���[�g�t�@�C����</TD>\n"
		   . "<TD><INPUT size=40 type=text name=temp value='".$set['temp']."'></TD>\n</TR>\n"
		   . "<TR>\n<TD bgcolor=#cccccc>���@���O�t�@�C����</TD>\n"
		   . "<TD><INPUT size=20 type=text name=logfilename value='".$set['logfilename']."'></TD>\n</TR>\n"
		   . "<TR>\n<TD bgcolor=#cccccc>���@�y�[�W���Ƃ̕\������</TD>\n"
		   . "<TD><INPUT size=10 type=text name=hyouji value='".$set['hyouji']."' style=\"text-align : right;\">��</TD>\n</TR>\n"
		   . "<TR>\n<TD bgcolor=#cccccc>���@���O�̕ۑ�����</TD>\n<TD>"
		   . "<INPUT size=10 type=text name=logcount value='".$set['logcount']."' style=\"text-align : right;\">��</TD>\n</TR>\n"
		   . "<TR>\n<TD bgcolor=#cccccc>���@���e�s�̃z�X�g��</TD>\n<TD>"
		   . "<INPUT size=80 type=text name=exhost value='".$set['exhost']."'></TD>\n</TR>\n"
		   . "<TR>\n<TD bgcolor=#cccccc>���@���e�s�̕�����</TD>\n<TD>"
		   . "<INPUT size=80 type=text name=exstr value='".$set['exstr']."'></TD>\n</TR>\n"
		   . "<TR>\n<TD bgcolor=#cccccc>���@�s���A�N�Z�X�`�F�b�N�����邽�߂̃p�X</TD>\n<TD>"
		   . "<INPUT size=80 type=text name=path value='".$set['path']."'></TD>\n</TR>\n"
		   . "<TR>\n<TD bgcolor=#cccccc>���@���e�`�F�b�N��</TD>\n<TD>"
		   . "<INPUT type=radio name=erchk value='ON'";
	if($set['erchk'] == "ON"){
		$print.=" checked";
	}
	$print	 .= ">����\n"
			  . "<INPUT type=radio name=erchk value='OFF'";
	if($set['erchk'] == "OFF"){
		$print.=" checked";
	}
	$print	 .= ">���Ȃ�\n</TD>\n</TR>\n"
			  . "<TR>\n<TD bgcolor=#cccccc>���@�y�[�W�ύX�p�����N������</TD>\n"
			  . "<TD><INPUT type=radio name=pagechange value=\"0\"";
	if($set['pagechange'] != "1"){
		$print .= " checked";
	}
	$print .= ">�S���\������<BR>\n"
			. "<input type=radio name=pagechange value=\"1\"";
	if($set['pagechange'] == "1"){
		$print .= " checked";
	}
	$print .= ">�O��<input type=text name=\"pagechangecount\" value=\"".$set['pagechangecount']."\" size=5 style=\"text-align : right;\">���\��</TD>\n</TR>\n"
			. "<TR>\n<TD bgcolor=#cccccc>���@�����N������̕\���p�^�[��</TD>\n"
			. "<TD><INPUT type=radio name=pattern value=\"0\"";
	if($set['pattern'] == "" || $set['pattern'] == "0"){
		$print .= " checked";
	}
	$print .= "> �@ [ <B>1</B> ] [ <A>2</A> ] [ <A>3</A> ]<BR>\n"
			. "<input type=radio name=pattern value=\"1\"";
	if($set['pattern'] == "1"){
		$print .= " checked";
	}
	$print .= "> �@ �y <B>1</B> �z �y <A>2</A> �z �y <A>3</A> �z<BR>\n"
			. "<input type=radio name=pattern value=\"2\"";
	if($set['pattern'] == "2"){
		$print .= " checked";
	}
	$print .= "> �@ <B>1</B> <A>2</A> <A>3</A>\n"
			. "</TD></TR>\n"
			. "<TR>\n<TD colspan=3 align=center><INPUT type=submit name=henkou value='�ݒ��ύX����'></TD>\n</TR>\n"
			. "<INPUT type=hidden name=mainset value=aaa>\n"
			. "</TABLE>\n";

	$script = "<SCRIPT language='JavaScript'>\n<!--\n";
	$script .= "function help(str){\n";
	$script .= "\tif(str == 'mainset'){	window.open('./help/mainset.html','','width=400,height=400,scrollbars');}\n";
	$script .= "}\n//-->\n</SCRIPT>";

	funcprint($script,'�X���b�h�f����1_PHP�Ł@�����ݒ�',$print);
}

##################################################################################
function funcprint($scr,$title,$pr) {
	global $set,$chstr;

	$settemp = readtemp('./temp/set.html');

	if($chstr){
		$settemp = preg_replace("/<!--CHSTR-->/","<FONT COLOR=RED>$chstr</FONT>",$settemp);
	}

	$settemp = preg_replace("/<!--SCRIPT-->/",$scr,$settemp);
	$settemp = preg_replace("/<!--TITLE-->/",$title,$settemp);
	$settemp = preg_replace("/<!--DATA-->/",$pr,$settemp);

	echo $settemp;
	exit;
}

function passchange($oldpass,$newpass) {
	global $set;
	if($oldpass == "" && $set['pass']){
		errorprint('���̓G���[','���݂̃p�X���[�h�̍��ڂ����͂���Ă��܂���');
	} if($newpass == ""){
		errorprint('���̓G���[','�ύX����p�X���[�h�̍��ڂ����͂���Ă��܂���');
	
	}
	$cnewpass = crypt($newpass,'ps');
	$coldpass = crypt($oldpass,'ps');
	if($set['pass'] != $coldpass && $set['pass']){
		errorprint('���̓G���[','�p�X���[�h���Ⴂ�܂��B���͂��Ȃ����Ă��������B');
	}
	setcookie("pass",$cnewpass);
	$set['pass'] = $cnewpass;
	return $cnewpass;
}

function setchange($setfile,$key,$value) {
	global $chstr;
	$data = "";
	$count = 0;
	foreach($key as $i){
		$data .= $i . "=" . $value[$count] . "\n";
		$count++;
	}
	if($SET = @fopen($setfile,"w")){
		flock($SET,LOCK_EX);
		fseek($SET,0,SEEK_SET);
		fwrite($SET,$data);
		fclose($SET);
	} elseif(file_exists($setfile)){
		errorprint('file Open Error!',"�t�@�C���̏������݂��o���܂���B�p�[�~�b�V�������m�F���Ă݂Ă��������B-> $setfile");
	} else{
		errorprint('file Open Error!',"�t�@�C�������݂��܂���B<BR>��̃t�@�C�����蓮�ō쐬���Ă��������B-> $setfile");
	}

	$chstr = "�ݒ��ύX���܂����B";
}

function setchange2($changestr,$changefile) {
	global $chstr;
	if($SET = @fopen($changefile,"a")){
		flock($SET,LOCK_EX);
		fseek($SET,0,SEEK_END);
		fwrite($SET,$changestr);
		fclose($SET);
	} elseif(file_exists($changefile)){
		errorprint("file Open Error!","�t�@�C���̏������݂��o���܂���B�p�[�~�b�V�������m�F���Ă݂Ă��������B-> $changefile");
	} else{
		errorprint('file Open Error!',"�t�@�C�������݂��܂���B<BR>��̃t�@�C�����蓮�ō쐬���Ă��������B-> $changefile");
	}
	$chstr = "�ݒ��ύX���܂����B";
}

function setchange3($changelist,$changefile){
	global $chstr;
	if($SET = @fopen($changefile,"w")){
		flock($SET,LOCK_EX);
		fseek($SET,0,SEEK_SET);
		fwrite($SET,$changelist);
		fclose($SET);
	} elseif(file_exists($changefile)){
		errorprint("file Open Error!","�t�@�C���̏������݂��o���܂���B�p�[�~�b�V�������m�F���Ă݂Ă��������B-> $changefile");
	} else{
		errorprint('file Open Error!',"�t�@�C�������݂��܂���B<BR>��̃t�@�C�����蓮�ō쐬���Ă��������B-> $changefile");
	}
	$chstr = "�ݒ��ύX���܂����B";
}

function setread($filename) {
	$data = array();

	if($LOGS = @fopen($filename,"r")){
		flock($LOGS,LOCK_SH);
		while(!feof($LOGS)){
			$line = fgets($LOGS);
			$line = preg_replace("/\r\n|\r|\n/","",$line);
			$setname = explode("=",$line);
			$name = $setname[0];
			$value = preg_replace("/^($name)=/","",$line);
			if($line != ""){
				$data[$setname[0]] = $value;
			}
		}
		fclose($LOGS);
	} else{
		errorprint("file open error","�t�@�C�����J���܂���B�t�@�C�������݂��邩�A�t�@�C���̃p�[�~�b�V�������m�F���Ă��������B-> $filename");
	}
	return $data;
}

function setread2($filename) {
	$data = array();

	if($LOGS = @fopen($filename,"r")){
		flock($LOGS,LOCK_SH);
		while(!feof($LOGS)){
			$line = fgets($LOGS);
			$line = preg_replace("/\r\n|\r|\n/","",$line);
			if($line != ""){
				array_push($data,$line);
			}
		}
		fclose($LOGS);
	} else{
		errorprint("file open error","�t�@�C�����J���܂���B�t�@�C�������݂��邩�A�t�@�C���̃p�[�~�b�V�������m�F���Ă��������B-> $filename");
	}
	return $data;
}

function readtemp($filename) {
	if($DAT = @fopen($filename,"r")){
		flock($DAT,LOCK_SH);
		$data = "";
		while(!feof($DAT)){
			$line = fgets($DAT);
			if(isset($line)){
				$data .= $line;
			}
		}
		fclose($DAT);
	} else{
		errorprint("File Open Error!","�t�@�C�����J���܂���B");
	}
	return $data;
}

function auth() {
	global $set;

	$clear = "";
	$error = "";
	$inpass = postget('pass');
	$cpass = crypt($inpass,"ps");
	if(isset($_COOKIE["pass"])){
		if($_COOKIE["pass"] == $set["pass"] && !$inpass){
			$clear = 1;
		}
	}
	if($cpass == $set["pass"]){
		$clear = 1;
	}
	if(!$set["pass"]){
		$clear = 1;
	}
	if($clear != 1){
		if($inpass){
			$error = "<FONT color='red'>�p�X���[�h����v���܂���I</FONT>";
		}
		$temp = readtemp("temp/admin.html");
		$temp = preg_replace("/<!--ERROR-->/",$error,$temp);
		echo $temp;
		exit;
	}
	if(isset($_COOKIE["pass"])){
		if($_COOKIE["pass"] == $set["pass"] && !$inpass){
			setcookie("pass",$set["pass"]);
		} elseif($inpass){
			setcookie("pass",$cpass);
		}
	} elseif($inpass){
		setcookie("pass",$cpass);
	}
	return $cpass;
}

function postget($name) {
	$input = "";
	if(isset($_POST[$name])){
		$input = $_POST[$name];
	}
	if($input == "" && isset($_GET[$name])){
		$input = $_GET[$name];
	}
	return($input);
}

function postarray($name) {
	$value = array();
	if(isset($_POST[$name]) && is_array(@$_POST[$name])){
			for($i = 0; $i < count(@$_POST[$name]); $i++){
				$input = $_POST[$name][$i];
				array_push($value,$input);
			}
	} elseif($name == "" && isset($_GET[$name])){
		$value[0] = $_GET[$name];
	}
	return($value);
}

function strtocode($str) {
	$code = "";
	for($i = 0; $i < strlen($str); $i++){
		$code .= "%" . dechex(ord(substr($str,$i,1)));
	}
	return($code);
}

function errorprint($title,$erstr) {
	echo "<HTML>\n<HEAD>\n";
	echo "<LINK rel=stylesheet href=cgigarage.css type=text/css>\n";
	echo "<TITLE>$title</TITLE>\n";
	echo "</HEAD>\n";
	echo "<BODY>\n";
	echo "<H1>$title</H1>\n";
	echo "<P>$erstr</P>\n";
	echo "<HR>\n";
	echo "<A href=\"https://cgi-garage.com/\">CGI-Garage</A>\n";
	echo "</BODY>\n";
	echo "</HTML>\n";
	exit;
}

?>