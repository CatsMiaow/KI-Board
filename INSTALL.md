## 설치 방법( INSTALL )

#### 파일 설명
\_Codeigniter → CI 코어 폴더  
www → 웹 기본 디렉터리로 public\_html 와 같은 의미입니다.  
사용하고 있는 호스팅 웹 폴더와 중첩되지 않도록 주의하세요.  
structure.sql → Database

폴더명으로 접근할 때 기본 파일은 main.php 입니다.  
app/config/routes.php의 default\_controller에 설정되어 있습니다.

---

1. 사용하고 있는 서버(또는 호스팅)의 웹 기본 폴더를 확인합니다. 일반적으로 www, public\_html

2. CI 코어 폴더를 웹 기본 폴더 밖에 업로드 합니다.

3. www 폴더 안의 파일을 웹 기본 폴더 안에 업로드 합니다.

4. database를 알아서 등록한다.

5. 폴더 권한 0707 설정

 * data 폴더 및 하위 폴더
 * app/cache
 * app/logs

6. app/config 환경 설정

 * config.php
     * (필수) $config['base\_url'] : 도메인 설정
     * (필수) $config['encryption\_key'] : 암호키 설정

 * constants.php
     * (필수) define('ADMIN', '') : 최고관리자 아이디 기입
     * (필수) define('ADM\_F', 'adm') : 관리자폴더 설정
     * define('RT\_PATH', '') : 최상위 경로가 아닐 경우 설정
     * $\_SERVER['DOCUMENT\_ROOT'] 값 끝에 / 가 붙으면 알아서 수정

 * cf\_basic.php → 공통 기본
 * cf\_board.php → 게시판
 * cf\_icon.php → 아이콘
 * cf\_register.php → 회원
 * (필수) database.php → DB

7. (필수) Apache Rewrite 설정 [ .htaccess ]

        RewriteEngine on
        RewriteCond %{REQUEST_URI} !^(/index\.php|/img/|/css/|/js/|/data/|/editor/)
        RewriteRule ^(.*)$ /index.php/$1 [L]

8. (필수) 관리자 아이디 생성

 * app/controllers/make.php ( http://도메인.com/make 접속으로 실행 )  
실행 전에 소스에서 아이디와 비번을 수정하세요.


#### 기타

CI 코더 폴더를 다른 경로에 위치할 때
index.php → $system\_path = '../\_Codeigniter'; // 상대경로 수정