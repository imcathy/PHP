<?php
    /*
        @param    - $arrayValid = validation 체크 값 배열형태
                    ex)
                        $valueValid = [
                            'idx' => ['type' => 'string', 'notnull' => true, 'default' => '', 'min' => 0, 'max' => 3],
                            'userId' => ['type' => 'string', 'notnull' => true, 'default' => '', 'min' => 2, 'max' => 20]
                        ];

                        type : 'string' / 'int'
                        notnull : true / false
                        min : int
                        max : int
        @return : request로 넘어온 모든 값을 key/value 형태로 리턴 
                    ex) Array( [param1] => value1 [param2] => value2 )
        @description
            - request로 넘어온 모든 값들에 대해 p + 파라미터명(첫글짜 대문자) 로 글로벌 변수가 선언된다.
            - request로 넘어온 모든 값들에 대해 $arrayValid 에 표기된 값들에 대한 validation 체크를 한다. → fnCheckParamValid()
            - request로 넘어온 모든 값들에 대해 Injection 및 XSS 공격 문자열 체크를 한다. → fnCheckParamValid()

    */
    
    function fnGetRequestParam($arrayValid = []){
        foreach ($_REQUEST as $key => $value){
            $keyName = 'p' . ucfirst($key);
            $validValue = array_key_exists($key, $arrayValid)? $arrayValid[$key] : []; // validation 설정
            if(is_array($value)){
                foreach($value as $arrayKey => $arrayValue){
                    $value[$arrayKey] = fnCheckParamValid($arrayValue, $validValue);
                }
                $GLOBALS[$keyName] = $value;
            }else{
                $GLOBALS[$keyName] = fnCheckParamValid($value, $validValue);
            }
        }

        // 옵션에는 설정되어 있지만 파라미터로 넘어오지 않은 값들에 대한 처리
        foreach ($arrayValid as $key => $validValue) {
            if (!array_key_exists($key, $_REQUEST)) {
                // 필수값 확인
                if (isset($validValue['notnull']) && $validValue['notnull']) {
                    fnShowAlertMsg('필수값이 존재하지 않습니다.', 'history.back()', true);
                }
                // 기본값 설정
                $keyName = 'p' . ucfirst($key);
                $GLOBALS[$keyName] = isset($validValue['default'])? $validValue['default'] : null;
            }
        }
    }

    /*
        @description 
            - fnGetRequestParam() 함수에서 실행
            - 모든 값들에 대해 Injection, XSS 공격체크를 함
            - $arrayValid 에 표기된 값들에 대해 validation 체크를 함
    */
    function fnCheckParamValid($value, $valid){    
        if(isset($valid['notnull']) && $valid['notnull'] && $value === '') fnShowAlertMsg('필수값이 존재하지 않습니다.', 'history.back()', true); // 필수값 확인

        if(isset($valid['type']) && $valid['type'] === 'int'){
            $value = fnNoInjection($value);
            if(!is_numeric($value)) fnShowAlertMsg('유효하지 않은 값입니다.', 'history.back()', true);
            $value = (int)$value;
            if(isset($valid['min']) && $value < $valid['min']) fnShowAlertMsg('유효하지 않은 값입니다.', 'history.back()', true);        
            if(isset($valid['max']) && $valid['max'] !== 0 && $value > $valid['max']) fnShowAlertMsg('유효하지 않은 값입니다.', 'history.back()', true); // max가 0일때는 체크하지 않음(무한대)
        }else{
            if(isset($valid['min']) && strlen($value) < $valid['min']) fnShowAlertMsg('유효하지 않은 값입니다.', 'history.back()', true);
            if(isset($valid['max']) && strlen($value) > $valid['max']) fnShowAlertMsg('유효하지 않은 값입니다.', 'history.back()', true);
            $value = fnNoInjection($value); // ; 문자열 길이가 달라질 수 있기 때문에 인젝션처리를 나중에 함
        }    
        return $value;
    }
    
    /*
        @desc : 
            - $str에 있는 인젝션 관련 문자는 공백으로 치환
            - $str에 있는 인젝션 관련 특수문자 앞에 / 추가
        @param : 
            - $str : 문자열
    */
    function fnSqlEscapeString($str){
        $str = preg_replace('/(and|or).*(union|select|insert|update|delete|from|where|limit|create|drop).*/i', '', $str);    
        $str = call_user_func('addslashes', $str);    
        return $str;
    }

    /*
        @desc : 
            - XSS 공격 방지
            - XSS  관련 문자열을 _XSS 를 붙여서 return 한다.
        @param : 
            - $str : 문자열
    */
    function fnNoXSS($str){
        // HTML EVENT 관련 XSS 공격 방지
        preg_match_all('#o(?:nerror|nload|nclick|nmouse|nscroll|ndbclick|nfocus|ninput)#i', $str, $arrayMatch, PREG_PATTERN_ORDER); // 정규식에 걸리는 문자열을 배열로 반환($arrayMatch)
        foreach($arrayMatch[0] as $matches){
            $str = str_replace($matches, '_XSS'.$matches, $str);
        }
        // < > XSS 공격 방지
        $str = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle|vg)|title|xml)[^>]*+>#i', '_XSS', $str);
        return $str;
    }

    /*
        @desc : Injection 공격 방지
        @param 
            - $str : 파라미터
    */
    function fnNoInjection($str){
        if (empty(strlen($str)) && empty(trim($str))) return;
        if(strpos($str, '&#') < 0) // 에디터에서 공백을 ; 로 치환하는 문제 수정
            $str = str_replace(';' , '&#59', $str);

        $str = str_replace("'" , '&#39', $str);        
        $str = str_replace("=\"ja" , '="&#106;a', $str);    
        $str = str_replace("=\"Ja" , '="&#74;a', $str);    
        $str = str_replace("=\"jA" , '="&#106;A', $str);    
        $str = str_replace("=\"JA" , '="&#74;A', $str);    
        $str = str_replace("=\'ja" , '="&#106;A', $str);        
        $str = str_replace("=\'Ja" , '="&#74;A', $str);        
        $str = str_replace("=\'jA" , '="&#106;A', $str);        
        $str = str_replace("=\'JA" , '="&#74;A', $str);                
        $str = str_replace('--' , '&#45&#45', $str);
        $str = str_replace('<sc' , '&lt;sc', $str);
        $str = str_replace('<Sc' , '&lt;Sc', $str);
        $str = str_replace('<sC' , '&lt;sC', $str);
        $str = str_replace('<SC' , '&lt;SC', $str);
        $str = str_replace('<sv' , '&lt;sv', $str);
        $str = str_replace('<Sv' , '&lt;Sv', $str);
        $str = str_replace('<sV' , '&lt;sV', $str);
        $str = str_replace('<SV' , '&lt;SV', $str);
        $str = str_replace('<if' , '&lt;if', $str);
        $str = str_replace('<If' , '&lt;If', $str);
        $str = str_replace('<iF' , '&lt;iF', $str);
        $str = str_replace('<IF' , '&lt;IF', $str);
        $str = str_replace('<object' , '&lt;object', $str);
        $str = str_replace('<xmp' , '&lt;xmp', $str);
        $str = str_replace('<embed' , '&lt;embed', $str);
        $str = str_replace('unescape' , '&#117nescape', $str);
        $str = str_replace('innerHTML' , '&#105nnerHTML', $str);
        $str = str_replace('../' , '&#46&#46&#47', $str);
        $str = str_replace('(' , '&#40;', $str);
        $str = str_replace(')' , '&#41;', $str);
        $str = fnNoXSS($str);
        $str = str_replace('..\\' , '&#46&#46&#92', $str); // trim 제거 (암호화 문자열 전달 시 의도적 공백이 trim 되어 복호화가 안되는 경우 발생 
        return $str;
    }

    function fnRevNoInjection($str){
        if (empty(strlen($str)) && empty(trim($str))) return;
        if(strpos($str, '&#') < 0) // 에디터에서 공백을 ; 로 치환하는 문제 수정
            $str = str_replace(';' , '&#59', $str);

        $str = str_replace("&#39" , "'", $str);        
        $str = str_replace('="&#106;a', "=\"ja", $str);    
        $str = str_replace('="&#74;a', "=\"Ja", $str);    
        $str = str_replace('="&#106;A', "=\"jA", $str);    
        $str = str_replace('="&#74;A', "=\"JA", $str);    
        $str = str_replace('="&#106;A', "=\'ja", $str);        
        $str = str_replace('="&#74;A', "=\'Ja", $str);        
        $str = str_replace('="&#106;A', "=\'jA", $str);        
        $str = str_replace('="&#74;A', "=\'JA", $str);                
        $str = str_replace('&#45&#45', '--', $str);
        $str = str_replace('&lt;sc', '<sc', $str);
        $str = str_replace('&lt;Sc', '<Sc', $str);
        $str = str_replace('&lt;sC', '<sC', $str);
        $str = str_replace('&lt;SC', '<SC', $str);
        $str = str_replace('&lt;sv', '<sv', $str);
        $str = str_replace('&lt;Sv', '<Sv', $str);
        $str = str_replace('&lt;sV', '<sV', $str);
        $str = str_replace('&lt;SV', '<SV', $str);
        $str = str_replace('&lt;if', '<if', $str);
        $str = str_replace('&lt;If', '<If', $str);
        $str = str_replace('&lt;iF', '<iF', $str);
        $str = str_replace('&lt;IF', '<IF', $str);
        $str = str_replace('&lt;object', '<object', $str);
        $str = str_replace('&lt;xmp', '<xmp', $str);
        $str = str_replace('&lt;embed', '<embed', $str);
        $str = str_replace('&#117nescape', 'unescape', $str);
        $str = str_replace('&#105nnerHTML', 'innerHTML', $str);
        $str = str_replace('&#46&#46&#47', '../', $str);
        $str = str_replace('&#40;', "(", $str);
        $str = str_replace('&#41;', ")", $str);
        //$str = fnNoXSS($str);
        $str = str_replace('&#46&#46&#92', '..\\', $str); // trim 제거 (암호화 문자열 전달 시 의도적 공백이 trim 되어 복호화가 안되는 경우 발생 
        return $str;
    }

    function fnHTMLDecode($str){
        return html_entity_decode($str);
    }

    /*
        @desc : 
            - value 문자열 양 옆에 ' 붙이고 문자열안에 '가 있을 경우 '' 로 치환
        @param : 
            - $str : 쿼문에 들어갈 value 값            
    */
    function fnCDBValue($str){
        $str = "'" . strtr($str, array("'"=>"''")) . "'";
        return $str;
    }

    /*
        @desc : 
            - 값이 null 일 경우 "NULL" 처리
            - value 문자열 양 옆에 ' 붙이고 문자열안에 '가 있을 경우 '' 로 치환
        @param : 
            - $str : 쿼문에 들어갈 value 값            
    */
    function fnCDBValue2($str){
        if(is_null($str)){ 
            $str = "NULL";
        }else{
            $str = "'" . strtr($str, array("'"=>"''")) . "'";
         }
        return $str;
    }

?>
