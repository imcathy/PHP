<?php
  /*
      @desc :
        - 엑셀 내용을 복사해서 textarea 에 붙여넣기 후 전송 한 값을 fnMakeExcelToArray() 함수를 거치면 엑셀 내용을 2차원 배열로 return 한다.
        - ex)	1	2	3	4
            5	6	7	8
            9	10	11	12
          => Array (
                [0] => Array ( [0] => 1 [1] => 2 [2] => 3 [3] => 4 )
                [1] => Array ( [0] => 5 [1] => 6 [2] => 7 [3] => 8 )
                [2] => Array ( [0] => 9 [1] => 10 [2] => 11 [3] => 12 )
             )
      @param :
        - $str : 엑셀 내용을 복사해서 textarea 에 붙여넣기 한 값
    */
    function fnMakeExcelToArray($str){
      $lineArray = explode("\n", $str);
      for($i=0;$i<sizeof($lineArray);$i++){
        $tabArray = explode("\t", $lineArray[$i]);
        for($j=0;$j<sizeof($tabArray);$j++){
          $lineArray[$i] = $tabArray;
        }
      }
      return $lineArray;
    }

    function fnIsMobile(){
      $userAgent = $_SERVER['HTTP_USER_AGENT'];
      $result = false;
      if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$userAgent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($userAgent,0,4))){
        $result = true;
      }
      return $result;
    }


    /*
      @desc :
        - 날짜 계산 함수
      @param :
        - $date : 날짜 ex) 2017-12-13, 2017-12-13 11:00
        - $dateType : year, month, day
        - $add : +1, -1
    */
    function fnCalDate($date, $dateType, $add){
      if($dateType == 'minutes'){
        return date('H:i', strtotime($date.$add.$dateType));
      }else{
        return date('Y-m-d', strtotime($date.$add.$dateType));
      }
    }

    function fnGetDate($strDate, $delimiter=''){
      if ($strDate == '') {
        $strDate = date('Y-m-d H:i:s');
      }
      switch($delimiter){
        case '-':
          $format = 'Y-m-d';
          break;
        case '.':
          $format = 'Y.m.d';
          break;
        case '/':
          $format = 'Y/m/d';
          break;
        case 'kor':
          $format = 'Y년 m월 d일';
          break;
        default:
          $format = 'Y-m-d H:i:s';
          break;
      }
      return date($format, strtotime($strDate));
    }

    function fnGetTime($strDate, $delimiter=''){
      switch($delimiter){
        case ':':
          $format = 'H:i';
          break;
        case 'kor':
          $format = 'H시 i분';
          break;
        case 'kor2':
          $hour = date("H", strtotime($strDate));
          $minute = date("i", strtotime($strDate));
          if($hour > 12) // 오전 오후 구분
            $ampm = '오후 ';
          else
            $ampm = '오전 ';

          if($minute!='00') // 분이 00으로 떨어질 때는 분을 붙이지 않는다.
            $minute = ' i분';
          else
            $minute = '';

          $format = $ampm . 'h시' . $minute;
          break;
        default:
          $format = 'H:i';
          break;
      }
      return date($format, strtotime($strDate));
    }
    function fnGetDayOfTheWeek($strDate){
      $result = '';
      if(!empty($strDate)){
        $strDate = substr($strDate, 0, 10);
        $day = array("일","월","화","수","목","금","토");
        $result = $day[date('w', strtotime($strDate))];
      }
      return $result;
    }

    /* 만 나이 구하기 */
    function fnGetAge($birthDay){
      $birthDateTime = strtotime($birthDay);
      $now = date('Ymd');
      $birthDay = date('Ymd', $birthDateTime);
      $age = floor(($now- $birthDay) / 10000);
      return $age;
    }

    function fnGetHost(){
      $host = '';
      if(isset($_SERVER['HTTPS'])){
        $host = 'https://';
      }else{
        $host = 'http://';
      }
      $host .= $_SERVER['HTTP_HOST'];
      return $host;
    }

    function fnSplitPhoneNumber($phoneNumber, $cp1, $cp2, $cp3){
      global $cp1;
      global $cp2;
      global $cp3;
      if(strpos($phoneNumber, '-')){
        $cpArray = explode('-', $phoneNumber);
        $cp1 = $cpArray[0];
        $cp2 = $cpArray[1];
        $cp3 = $cpArray[2];
      }else if(strlen($phoneNumber)==10){
        $cp1 = substr($phoneNumber, 0, 3);
        $cp2 = substr($phoneNumber, 3, 3);
        $cp3 = substr($phoneNumber, 6, 4);
      }else{
        $cp1 = substr($phoneNumber, 0, 3);
        $cp2 = substr($phoneNumber, 3, 4);
        $cp3 = substr($phoneNumber, 7, 4);
      }
    }

    /*
        @desc :
            - 마스킹
        @param :
            - $data : 데이터
            - $type :
                kName        - 한글이름(2번째 글자만 * 변환)
                eName        - 영어이름(5번째 글자 이후 모두 * 변환)
                tel            - 일반전화(국번이후 숫자 4개 * 변환)
                hp            - 일반전화(국번이후 숫자 4개 * 변환)
                inTel        - 국제전화(앞 3글자 이후 숫자 4개 * 변환)
                addr        - 주소(5번째 글자 이후 모두 * 변환)
                email        - 이메일(5번째 글자 이후 모두 * 변환)
                account        - 계좌번호(5번째 글자 이후 모두 * 변환)
                birth        - 생년월일(4번째 글자 이후 모두 * 변환)
                compNum        - 사번,학번,군번(5번째 글자 이후 모두 * 변환)
                docNum        - 문서확인번호(앞4자리만)
                reportNum    - 증명서발급번호(끝4자리만)
                모든 타입은 입력받은 문자열 길이가 변환에 필요한 문자열 길이가 짧을 경우 변환없이 문자열 그대로 출력됩니다.
    */
    function fnMasking($type, $data){

        $data= trim($data);
        $maskingValue = $data;

        $useHyphen = "-";

        if($data != ""){
            if($type == 'kName'){
                $strlen = mb_strlen($data, 'utf-8');
                switch($strlen){
                    case 2:
                        $maskingValue = mb_substr($data, 0, 1, "UTF-8").'*';
                    break;
                    case 3:
                        $maskingValue = mb_substr($data, 0, 1, "UTF-8").'*'.mb_substr($data, 2, 3, "UTF-8");
                    break;
                    case 4:
                        $maskingValue = mb_substr($data, 0, 1, "UTF-8").'**'.mb_substr($data, 3, 4, "UTF-8");
                    break;
                    default:
                        $maskingValue = mb_substr($data, 0, 1, "UTF-8").'**'.mb_substr($data, 3, 4, "UTF-8");
                    break;
                }
            }else if($type == 'eName'){
                $maskingValue = fnMaskingLeftCut( $data, 5 );
            }else if($type == 'tel'){
                $ptn = '/([0-9]+)-([0-9]+)-([0-9]{4})/';
                $re    = '$1-****-$3';
                $maskingValue = preg_replace($ptn, $re, $data);
            }else if($type == 'hp'){
                $ptn = '/([0-9]+)-([0-9]+)-([0-9]{4})/';
                $re    = '${1}-****-$3';
                $maskingValue = preg_replace($ptn, $re, $data);
            }else if($type == 'inTel'){
                $ptn = '/([0-9]+)-([0-9]+)-([0-9]{4})/';
                $re    = '${1}-****-$3';
                $maskingValue = preg_replace($ptn, $re, $data);
            }else if($type == 'addr'){
                $maskingValue = fnMaskingLeftCut( $data, 5 );
            }else if($type == 'email'){
                $maskingValue = fnMaskingLeftCut( $data, 5 );
            }else if($type == 'account'){
                $maskingValue = fnMaskingLeftCut( $data, 5 );
            }else if($type == 'birth'){
//                $maskingValue = fnMaskingLeftCut( $data, 4 );
                $maskingValue = fnMaskingCut( $data, 2, 'l' );
            }else if($type == 'compNum'){
                $maskingValue = fnMaskingLeftCut( $data, 5 );
            }else if($type == 'docNum'){
                $maskingValue = fnMaskingCut( $data, 4, 'f' );
            }else if($type == 'reportNum'){
                $maskingValue = fnMaskingCut( $data, 4, 'l' );
            }
        }

        return $maskingValue;
    }

    /*
        @desc :
            - 문자길이 이후 마스킹 처리
        @param :
            - $data        : 데이터
            - $cutlen    : 문자길이
    */
    function fnMaskingLeftCut($data, $cutlen){
        $data= trim($data);
        $strlen = mb_strlen($data, 'utf-8');

        $returnStr = $data;

        if( $strlen > $cutlen ){
            $returnStr = mb_substr($data, 0, $cutlen, "UTF-8");
            $tmplen = $strlen-$cutlen;
            if( $tmplen > 10 ){
                $tmplen = 10;
            }
            for( $i=0; $tmplen > $i; $i++ ){
                $returnStr .= "*";
            }
        }

        return $returnStr;
    }

    /*
        @desc :
            - $type 따라 문자길이 만 마스킹 처리 (앞부터 4 또는 뒤로 4)
        @param :
            - $data        : 데이터
            - $cutlen    : 문자길이
            - $type        : f 처음, l 마지막, c 중간, a 전부
    */
    function fnMaskingCut($data, $cutlen, $type ){
        $data= trim($data);
        $strlen = mb_strlen($data, 'utf-8');

        $returnStr = $data;

        if( $strlen > $cutlen ){
            if( $type == "f" ){
                $returnStr = "";
                for( $i=0; $cutlen > $i; $i++ ){
                    $returnStr .= "*";
                }
                $returnStr .= mb_substr($data, $cutlen, $strlen, "UTF-8");
            }else if( $type == "l" ){
                $returnStr = mb_substr($data, 0, $strlen-$cutlen, "UTF-8");
                for( $i=0; $cutlen > $i; $i++ ){
                    $returnStr .= "*";
                }
            } else if ($type == 'c') {
                if ($strlen < 3) {
                    for ($i=0;$i<$strlen-1; $i++) {
                        $returnStr = mb_substr($data, 0, 1, 'UTF-8') . '*';
                    }
                } else {
                    for ($i=0;$i<$strlen-2; $i++) {
                        $returnStr = mb_substr($data, 0, 1, 'UTF-8') . '*';
                    }
                    $returnStr .= mb_substr($data, -1, 1, 'utf-8');
                }            
            } else if ($type == 'a') {
                $returnStr = str_repeat('*', $strlen);
            }
        }

        return $returnStr;
    }    
  ?>
