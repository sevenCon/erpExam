<?php
//var_dump($single_add);
//var_dump($multiply_add);
$oneques_single_add=array();
$oneques_multiply_add=array();
  if(!empty($single_add))
    foreach($single_add as $ksa=>$vsa)
    {
	   if($vsa['questions_flow_id']==$question_id)
       $oneques_single_add[]=$vsa;//某个问题ID的所有正确单选附加题。这个可能为空，某个步骤内可能全为多选题。
    }

//var_dump($question_id);
  if(!empty($multiply_add))
    foreach($multiply_add as $kma=>$vma)
    {
        if($vma['questions_flow_id']==$question_id)
	    $oneques_multiply_add[]=$vma;//多选附加题，也可能为空
	
    }

	//var_dump($oneques_multiply_add);




//var_dump($score_rate);
//var_dump($one_question_score['score']);

//var_dump((1-$exam_inf['score_step_add']));
//echo "`````````````````````````````";

//var_dump(count($question_add_id));

	$one_score=$score_rate*$one_question_score['score']*(1-$exam_inf['score_step_add'])/$max/count($question_add_id);//附加题，一道题的分值。
//var_dump($one_score);

  if(!empty($oneques_single_add))
  {
    foreach($question_add_id as $kqai1=>$vqai1)
	{
	//	var_dump($vqai1['questions_add_id']);
	  foreach($oneques_single_add as $kosa=>$vosa)
	   {

		  
         if($vqai1['questions_add_id']==$vosa['questions_add_id'])

			 $correct_useransw_id1[]=$vosa['useransw_id'];
	   }
	}

	if(!empty($correct_useransw_id1))
	{
	  $ii=count($correct_useransw_id1);
      $str_correct_useransw_id1="useransw_id=".$correct_useransw_id1[0];
	  for($z=1;$z<$ii;$z++)
	  {
	    $str_correct_useransw_id1.=" or useransw_id=".$correct_useransw_id1[$z];
	  }//1||2||3，把每个组的ID得出，然后由下面查出所有组的用户ID、


	  $str_update_addsingle_score="update flow_examlog_useransw_add set examlog_score=".$one_score." where ".$str_correct_useransw_id1;


	  $conn_db->execute_dml($str_update_addsingle_score);

	}//if(!empty($correct_useransw_id))

  } //if(!empty($onesingle_add))


     




   //以下为附加题多选题判分
   if(!empty($oneques_multiply_add))
  {

//  var_dump($question_add_id);
    foreach($question_add_id as $kqai2=>$vqai2)
	{//每次循环一题附加题
		$correct_useransw_id2=array();
         $vqai2['paperquestions_id']=null;
		 $vqai2['answers_other_question_id']=null;
		//var_dump($oneques_multiply_add);
	  foreach($oneques_multiply_add as $koma=>$voma)
	   {//每次循环一题附加题的一个选项
	//	 var_dump($vqai2['questions_add_id']);
	//	var_dump($voma['questions_add_id']);
         if($vqai2['questions_add_id']==$voma['questions_add_id'])
		   {
			 $correct_useransw_id2[]=$voma['useransw_id'];//一题附加题所有选项
			 if($vqai2['paperquestions_id']==null)
			 {
			   $vqai2['paperquestions_id']=$voma['paperquestions_id'];
               $vqai2['answers_other_question_id']=$voma['answers_other_question_id'];
			 }
		   }

	
	   }
	//   var_dump($vqai2['questions_add_id']);
	 //  var_dump($vqai2['paperquestions_id']);
	 //  var_dump($vqai2['answers_other_question_id']);
	//   var_dump($correct_useransw_id2);
	     $nums_correct_option=0;//正确选项个数
		 $nums_user_option=0;//考生选项个数
		 $nums_standard_option=0;//标准答案选项个数。
		

	   	if(!empty($correct_useransw_id2))
		{
			$nums_correct_option=count($correct_useransw_id2);//正确选项个数


			$str_nums_user_option="select count(useransw_id) from flow_examlog_useransw_add where questions_add_id=".$vqai2['questions_add_id']." and paperquestions_id=".$vqai2['paperquestions_id']." and examination_log_id=".$examination_log_id;
		    $r3=$conn_db->execute_dql1($str_nums_user_option);
		    
			if($r3)
                $nums_user_option=current($r3);//考生选项个数
            else
				$nums_user_option=1;

			//var_dump($nums_user_option);



			$str_nums_standard_option="select count(answers_other_id) from flow_answers_other where answers_is_add=1 and answers_other_isright=1 and answers_other_question_id=".$vqai2['answers_other_question_id'];
            $r4=$conn_db->execute_dql1($str_nums_standard_option);
		   

			
			if($r4)
                 $nums_standard_option=current($r4);//标准答案选项个数。
			else
                $nums_standard_option=0;

		//	var_dump($nums_standard_option);

			if($nums_user_option>$nums_correct_option)//有错误
			 {
			    $str_update_muladd_score="update flow_examlog_useransw_add set examlog_score=0 where paperquestions_id=".$vqai2['paperquestions_id']." and examination_log_id=".$examination_log_id." and questions_add_id=".$vqai2['questions_add_id'];

                $conn_db->execute_dml($str_update_mulconadd_score);//将该题所有答案选项分数置0；
			 }

			 else //考生答案没有错误选项
			  {
			   if($nums_standard_option!=0)
                 $option_score_add=$nums_user_option/$nums_standard_option;//这里只是当分值为1时，若分数不是1，这里只是比率
			   else
                 $option_score_add=0;
		   
		       $nums_uid_add=count($correct_useransw_id2);
			
			   $str_correct_useransw_id2="useransw_id=".$correct_useransw_id2[0];
			   for($bb=1;$bb<$nums_uid_add;$bb++)
			   {$str_correct_useransw_id2.=" OR useransw_id=".$correct_useransw_id2[$bb];}
		  
      // $pp[]=$voma['questions_add_id'];
		//var_dump($pp);
	//	echo "djcoiwhoincvweoh";
	//var_dump($str_correct_useransw_id2);
		// if($voma['questions_add_id']==3)
		//		  {  var_dump($nums_user_option);
		// echo "111111`````````22132";}
               $str_update_muladd_score="update flow_examlog_useransw_add set examlog_score=".($one_score*$option_score_add/$nums_user_option)." where paperquestions_id=".$vqai2['paperquestions_id']." and examination_log_id=".$examination_log_id." and ".$str_correct_useransw_id2;//更新多选题正确选项的分数

               $conn_db->execute_dml($str_update_muladd_score);
			  }
		
		}//if(!empty($correct_useransw_id2))
	}//foreach($question_add_id as $kqai2=>$vqai2)

  
  
  
  
  }//if(!empty($oneques_multiply_add))




?>