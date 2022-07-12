<?php
    function fnOpenDB($dbHost, $dbUser, $dbPasswd) {
        $dbConn = null;
        if(empty($dbConn)){
            try{
                $dbConn = new PDO ($dbHost, $dbUser, $dbPasswd);
                $dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // 예외를 처리 한다. 
                $dbConn->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, true); // 예외를 처리 한다. 
            } catch (Exception $e) {
                die('Connection 시작 중 오류가 발생했습니다. <br><br>' . $e->getMessage());
            }
        }
        return $dbConn;
    }

    function fnSQLSelect($dbConn, $sqlText, $userParam=[]){
        return fnSQLPrepareExecute($dbConn, $sqlText, 'select', $userParam);
    }

    function fnSQLSelectAll($dbConn, $sqlText, $userParam=[]){
        return fnSQLPrepareExecute($dbConn, $sqlText, 'selectAll', $userParam);
    }

    function fnSQLExecute($dbConn, $sqlText, $userParam=[]){
        return fnSQLPrepareExecute($dbConn, $sqlText, 'execute', $userParam);
    }

    function fnSQLReturn($dbConn, $sqlText, $userParam=[]){
        return fnSQLPrepareExecute($dbConn, $sqlText, 'return', $userParam);
    }

    function fnSQLPrepareExecute($dbConn, $sqlText, $sqlMethod, $userParam=[]){
        if($sqlMethod==='return'){ // $sqlMethod 가 return 일 경우 return 값을 받을 수 있는 형태의 쿼리문으로 변환한다.
            if(stripos(trim($sqlText), 'DECLARE')!==0 && stripos($sqlText, 'EXEC ')===0){
                $sqlText = substr($sqlText, 5);
            }
            $sqlText = 'DECLARE @result INT; EXEC @result = '.$sqlText.'; SELECT @result AS result;';                
        }

        $stmt = $dbConn->prepare($sqlText, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY) );
        $stmt->execute(fnSQLPrepareParam($sqlText, $userParam));

        try{
            if($sqlMethod==='select'){
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }else if($sqlMethod==='selectAll'){
                do{
                    $result[] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }while ($stmt->nextRowset());
            }else if($sqlMethod==='execute'){
                $result = $stmt->rowCount();
            }else if($sqlMethod==='return'){
                $result = $stmt->fetchColumn();
            }
        }catch(PDOException $e){
            echo '- [Message] : '. $e->getMessage() . '<br>';
            echo '- [File] : '. $e->getTrace()[1]['file'] . '<br>';
            echo '- [Line] : '. $e->getTrace()[1]['line'] . '<br>';
            echo '- [Query] : '. $sqlText .'<br>';
            echo '- [Param] : ';
            echo '<br><br><br><br><br>';
            echo '- [Error] : 쿼리를 실행할 수 없습니다.<br><br>'; 
        } finally {
            $stmt = null;
        }
        return $result; // arrayName[rowCnt][ColName] 형태로 return (PDO::FETCH_ASSOC)
    }

    function fnSQLPrepareParam($sqlText, $userParam) {
        preg_match_all('/:([0-9a-zA-Z_]+)/', $sqlText, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $key = $match[0];
            if (array_key_exists($key, $userParam)) {
                $value = $userParam[$key];
            }else {
                // 전역변수 ($val)
                $keyGlobal = substr($key, 1);
                // REQUEST 파라미터 ($pVal)
                $keyParam = 'p'.ucfirst($keyGlobal);

                if (isset($GLOBALS[$keyParam])) {
                    $value = $GLOBALS[$keyParam];
                }else {
                    $value = (isset($GLOBALS[$keyGlobal]))? $GLOBALS[$keyGlobal] : null;
                }
            }
            // 값이 배열일 경우 콤마(,)로 연결하여 문자열 생성
            $param[$key] = (is_array($value))? implode(',', $value) : $value;
        }
        return $param;
    }

    function fnGetSQL($sqlText, $userParam = []) {
        $param = fnSQLPrepareParam($sqlText, $userParam);

        foreach ($param as $key => $value) {
            if (is_null($value)) {
                $value = 'null';
            }else if (is_int($value) || is_float($value)) {
                $value = (string)$value;
            }else {
                $value = '\''.str_replace('\'', '\'\'', $value).'\'';
            }
            $sqlText = str_replace($key, $value, $sqlText);
        }
        return $sqlText;
    }

    /* 
        트랜잭션 처리를 개별적으로 할 경우 사용할 함수 
            fnBeginTransaction();
            fnCommit();
            fnRollback();
        fnSQLExecute() 와 함께 사용할 필요 없다.
    */
    function fnBeginTransaction($dbConn){
        try{
            $dbConn->beginTransaction();
        }catch(Exception $e){
            die('트랜잭션을 실행하던 중 오류가 발생했습니다.<br><br>' . $e);
        }
    }

    function fnCommit($dbConn){
        try{
            $dbConn->commit();
        }catch(Exception $e){
            echo '- [Message] : '. $e->getMessage() . '<br>';
            echo '- [File] : '. $e->getTrace()[1]['file'] . '<br>';
            echo '- [Line] : '. $e->getTrace()[1]['line'] . '<br>';
            echo '- [Query] : '. $sqlText .'<br><br><br><br><br>';
            die('커밋을 실행하던 중 오류가 발생했습니다.<br><br>' . $e);
        }
    }

    function fnRollback($dbConn){
        try{
            $dbConn->rollback();
        }catch(Exception $e){
            die('롤백을 실행하던 중 오류가 발생했습니다.<br><br>' . $e);
        }
    }
?>
