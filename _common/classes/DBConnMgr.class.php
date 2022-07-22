<?php
	/*
		@description : DB 커넥션 클래스 및 쿼리 관련 함수
	*/
	class DBConnMgr extends PDO{
		public $dbInfo; // DB Driver 정보
		public $dbUser; // DB 아이디
		public $dbPw; // DB 패스워드

		public $_conn; // connection 
		public $_stmt; // statement
		public $_result; // 결과값
		public $_paramArray;
				
		/* 생성자 - 전달받은 정보를 전역 변수에 담는다 */
		public function __construct($dbInfo, $dbUser, $dbPw){ 
			$this->dbInfo = $dbInfo;
			$this->dbUser = $dbUser;
			$this->dbPw = $dbPw;
		}

		/* DB connection open */
		function fnOpenDB(){
			if(empty($this->_conn)){
				try{
					$this->_conn = new PDO ($this->dbInfo, $this->dbUser, $this->dbPw);
					$this->_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // 예외를 처리 한다. 
					$this->_conn->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, true); // 예외를 처리 한다. 
					$this->_result = null;
				} catch (Exception $e) {
					//$error =$this->_conn->errorInfo();
					//die("Connection 시작 중 오류가 발생했습니다. : SQL Error={$error[0]}, DB Error={$error[1]}, Message={$error[2]}");
					die('Connection 시작 중 오류가 발생했습니다. <br><br>' . $e->getMessage());
				}
			}
		}

		/* DB connection close */
		function fnCloseDB(){
			try{
				if($this->_conn) { 
					$this->_stmt = null;
					$this->_conn = null;
				}
			}catch(Exception $e){
				die('Connection 종료 중 오류가 발생했습니다. <br><br> ' . $e->getMessage());
			}
		}

		function fnSQLSelect($sqlText, $userParam=[]){
			return $this->fnSQLPrepareExecute($sqlText, 'select', $userParam);
		}

		function fnSQLSelectAll($sqlText, $userParam=[]){
			return $this->fnSQLPrepareExecute($sqlText, 'selectAll', $userParam);
		}

		function fnSQLExecute($sqlText, $userParam=[]){
			return $this->fnSQLPrepareExecute($sqlText, 'execute', $userParam);
		}

		function fnSQLReturn($sqlText, $userParam=[]){
			return $this->fnSQLPrepareExecute($sqlText, 'return', $userParam);
		}

		function fnSQLPrepareExecute($sqlText, $sqlMethod, $userParam=[]){
			$this->fnOpenDB(); // DB connect - $this->_conn 에 커넥션 정보가 담긴다.

			if($sqlMethod==='return'){ // $sqlMethod 가 return 일 경우 return 값을 받을 수 있는 형태의 쿼리문으로 변환한다.
				if(stripos(trim($sqlText), 'DECLARE')!==0 && stripos($sqlText, 'EXEC ')===0){
					$sqlText = substr($sqlText, 5);
				}
				$sqlText = 'DECLARE @result INT; EXEC @result = '.$sqlText.'; SELECT @result AS result;';				
			}
			
			$this->_stmt = $this->_conn->prepare($sqlText, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY) );
			$this->_stmt->execute($this->fnSQLPrepareParam($sqlText, $userParam));

			try{
				if($sqlMethod==='select'){
					$this->_result = $this->_stmt->fetchAll(PDO::FETCH_ASSOC);
				}else if($sqlMethod==='selectAll'){
					do{
						$this->_result[] = $this->_stmt->fetchAll(PDO::FETCH_ASSOC);
					}while ($this->_stmt->nextRowset());
				}else if($sqlMethod==='execute'){
					$this->_result = $this->_stmt->rowCount();
				}else if($sqlMethod==='return'){
					$this->_result = $this->_stmt->fetchColumn();					
				}	
			}catch(PDOException $e){
				echo '- [Message] : '. $e->getMessage() . '<br>';
				echo '- [File] : '. $e->getTrace()[1]['file'] . '<br>';
				echo '- [Line] : '. $e->getTrace()[1]['line'] . '<br>';
				echo '- [Query] : '. $sqlText .'<br>';
				echo '- [Param] : ';
				echo '<br><br><br><br><br>';
				echo '- [Error] : 쿼리를 실행할 수 없습니다.<br><br>'; 
			}finally{
				if(!$this->_conn->inTransaction()){ // 실행된 트랜잭션이 존재하지 않을 경우에만 fnCloseDB() 실행
					$this->fnCloseDB(); // $this->_conn 을 null 처리 하여 커넥션 close 처리
				}		
			}
			return $this->_result; // arrayName[rowCnt][ColName] 형태로 return (PDO::FETCH_ASSOC)
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
			$param = $this->fnSQLPrepareParam($sqlText, $userParam);

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
		function fnBeginTransaction(){
			$this->fnOpenDB();
			try{
				$this->_conn->beginTransaction();
			}catch(Exception $e){
				die('트랜잭션을 실행하던 중 오류가 발생했습니다.<br><br>' . $e);
			}
		}

		function fnCommit(){
			try{
				$this->_conn->commit();
			}catch(Exception $e){
				echo '- [Message] : '. $e->getMessage() . '<br>';
				echo '- [File] : '. $e->getTrace()[1]['file'] . '<br>';
				echo '- [Line] : '. $e->getTrace()[1]['line'] . '<br>';
				echo '- [Query] : '. $sqlText .'<br><br><br><br><br>';
				die('커밋을 실행하던 중 오류가 발생했습니다.<br><br>' . $e);
			}finally{
				$this->fnCloseDB();
			}
		}

		function fnRollback(){
			try{
				$this->_conn->rollback();
			}catch(Exception $e){
				die('롤백을 실행하던 중 오류가 발생했습니다.<br><br>' . $e);
			}finally{
				$this->fnCloseDB();
			}
		}

		function fnClear(){
			$this->_conn = null;
			$this->_result = null;
			$this->dbInfo = null;
			$this->dbUser = null;
			$this->dbPw = null;
		}

?>
