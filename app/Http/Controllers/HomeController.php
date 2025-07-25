<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use App\Models\StaffView;
use App\Models\Staff;

class HomeController extends Controller
{
    public function index()
    {                
        $schools_id = config('ge.schools_id');
        $att = session('gsuite_login');
        $staffs = [];
        $teachers = [];
        $all_teachers = [];
        $users = [];
        $error_users = [];
        $big_error_users1 = [];
        $big_error_users2 = [];
        $all_error_users = [];
        $yahoo_error_users = [];
        $check_users = [];
        $all_staffs_count = 0;
        $all_users_count = 0;
        if(!empty($att)){            
            if($att['login']){   
                $staffs = Staff::where('staff_sid', session('gsuite_login.school_code'))    
                ->where('staff_status', 1)   
                ->where('staff_kind','<>', '學生')     
                ->get();
                foreach($staffs as $staff) {
                    $pid = mb_convert_kana($staff->staff_plan_id, 'as');
                    $pid = str_replace(' ', '', $pid);
                    $pid = strtoupper($pid);                                                    
                    $teachers[$pid]['name'] = $staff->staff_name;        
                    $teachers[$pid]['title'] = $staff->staff_title; 	                                       
                }      
                
                //dd($teachers);
                
                if(is_file(storage_path('app/privacy/all.csv'))){
                    $csvFile = storage_path('app/privacy/all.csv');                    
                    if (($handle = fopen($csvFile, 'r')) !== false) {
                        while (($data = fgetcsv($handle, 1000, ',')) !== false) {        
                            $all_users_count++;                    
                            $pid = mb_convert_kana($data[1], 'as');
                            $pid = str_replace(' ', '', $pid);
                            $pid = strtoupper($pid);                                
                            if(!$this->isValidTaiwanID($data[1])){                                                                
                                $error_users[$data[3]]['date'] = $data[0];
                                $error_users[$data[3]]['pid'] = $pid;
                                $error_users[$data[3]]['gsuite'] = $data[3];
                                $error_users[$data[3]]['agree'] = $data[4];
                            }

                            if($this->containsFullWidth($data[1])){
                                $big_error_users1[$data[1]]['date'] = $data[0];
                                $big_error_users1[$data[1]]['pid'] = $pid;
                                $big_error_users1[$data[1]]['gsuite'] = $data[3];
                                $big_error_users1[$data[1]]['agree'] = $data[4];
                                $big_error_users1[$data[1]]['mail'] = $data[2];
                            }                            

                            if(!empty($pid)){
                                $users[$pid]['date'] = $data[0];
                                $users[$pid]['pid'] = $pid;
                                $users[$pid]['gsuite'] = $data[3];
                                $users[$pid]['agree'] = $data[4];                            
                                $users[$pid]['mail'] = $data[2];     
                            }
                            
                        }                    
                        fclose($handle);
                    } else {
                        echo "無法開啟檔案";
                    }                    
                    if(($att['name']=="王麒富" and $att['school_code']=="074628") or 
                    ($att['name']=="林哲民" and $att['school_code']=="079998") or
                    ($att['name']=="林金玉" and $att['school_code']=="079998") or
                    ($att['name']=="林政言" and $att['school_code']=="079998")){
                        $all_staffs = Staff::where('staff_kind','<>', '學生')     
                        ->where('staff_status', 1)
                        ->get();
                        $all_staffs_count = $all_staffs->count();
                        foreach($all_staffs as $staff) {
                            if($this->containsFullWidth($staff->staff_plan_id)){
                                $big_error_users2[$staff->staff_plan_id]['school'] = $schools_id[$staff->staff_sid]; 
                                $big_error_users2[$staff->staff_plan_id]['name'] = $staff->staff_name; 
                                $big_error_users2[$staff->staff_plan_id]['title'] = $staff->staff_title;                                 
                            }

                            $pid = str_replace(' ', '', $staff->staff_plan_id);
                            $pid = strtoupper($pid);                                
                            $pid = mb_convert_kana($pid, 'as');
                            $all_teachers[$pid]['name'] = $staff->staff_name;        
                            $all_teachers[$pid]['title'] = $staff->staff_title; 	                                                                     
                        }
                        foreach($users as $k => $v) {
                            if(!isset($all_teachers[$k])){
                                $all_error_users[$k]['date'] = $users[$k]['date'];
                                $all_error_users[$k]['pid'] = $users[$k]['pid'];
                                $all_error_users[$k]['check_pid'] = ($this->isValidTaiwanID($users[$k]['pid'])) ?true:false;
                                $all_error_users[$k]['gsuite'] = $users[$k]['gsuite'];
                                $all_error_users[$k]['agree'] = $users[$k]['agree'];
                                $all_error_users[$k]['mail'] = $users[$k]['mail'];
                            }

                            if(strpos($v['mail'], '@yahoo') or strpos($v['mail'], '@YAHOO') !== false) {
                                $yahoo_error_users[$k]['date'] = $users[$k]['date'];
                                $yahoo_error_users[$k]['pid'] = $users[$k]['pid'];
                                $yahoo_error_users[$k]['check_pid'] = ($this->isValidTaiwanID($users[$k]['pid'])) ?true:false;
                                $yahoo_error_users[$k]['gsuite'] = $users[$k]['gsuite'];
                                $yahoo_error_users[$k]['agree'] = $users[$k]['agree'];
                                $yahoo_error_users[$k]['mail'] = $users[$k]['mail'];
                            }

                        }
                    }
                }
                
                foreach($teachers as $k => $v) {
                    if(isset($users[$k])){
                        $check_users[$k]['date'] = $users[$k]['date'];                                                
                        $pid = $this->hideAccount($users[$k]['pid']);
                        $check_users[$k]['pid'] = $pid;
                        $check_users[$k]['agree'] = $users[$k]['agree'];
                        $check_users[$k]['mail'] = $users[$k]['mail'];
                        $v['name'] = $this->hideMiddleChineseName($v['name']);
                        $check_users[$k]['name'] = $v['name'];
                        $check_users[$k]['title'] = $v['title'];
                        $gsuite = str_replace('@chc.edu.tw', '', $users[$k]['gsuite']);
                        $gsuite = $this->hideAccount($gsuite);
                        $check_users[$k]['gsuite_account'] = $gsuite;
                    }
                }                
            }                            
        }

        //dd($big_error_users2);
        
        
        $data = [
            'check_users' => $check_users,
            'error_users' => $error_users,     
            'big_error_users1'=>$big_error_users1,
            'big_error_users2'=>$big_error_users2,
            'yahoo_error_users'=>$yahoo_error_users,
            'all_error_users' => $all_error_users,   
            'all_users_count'=> $all_users_count,
            'all_staffs_count'=> $all_staffs_count,
        ];
        return view('index',$data);
    }

    public function glogin(){
        $key = rand(10000, 99999);
        session(['chaptcha' => $key]);

        $data = [

        ];
        return view('glogin',$data);
    }

    public function pic()
    {
        //$key = rand(10000, 99999);
        //session(['chaptcha' => $key]);
        $key = session('chaptcha');

        $back = rand(0, 9);
        /*
        $r = rand(0,255);
        $g = rand(0,255);
        $b = rand(0,255);
        */
        $r = 0;
        $g = 0;
        $b = 0;        

        //$cht = array(0=>"零",1=>"壹",2=>"貳",3=>"參",4=>"肆",5=>"伍",6=>"陸",7=>"柒",8=>"捌",9=>"玖");
        $cht = array(0 => "0", 1 => "1", 2 => "2", 3 => "3", 4 => "4", 5 => "5", 6 => "6", 7 => "7", 8 => "8", 9 => "9");
        $cht_key = "";
        for ($i = 0; $i < 5; $i++) $cht_key .= $cht[substr($key, $i, 1)];

        header("Content-type: image/gif");
        $im = imagecreatefromgif(asset('images/back.gif')) or die("無法建立GD圖片");
        $text_color = imagecolorallocate($im, $r, $g, $b);

        imagettftext($im, 25, 0, 5, 32, $text_color, public_path('font/AdobeGothicStd-Bold.otf'), $cht_key);
        imagegif($im);
        imagedestroy($im);
    }

    public function gauth(Request $request)
    {
        if ($request->input('chaptcha') != session('chaptcha')) {
            return back()->withErrors(['gsuite_error' => ['驗證碼錯誤！']]);
        }
        $username = explode('@', $request->input('username'));

        $data = array("email" => $username[0], "password" => $request->input('password'));
        $data_string = json_encode($data);
        $ch = curl_init(env('GSUITE_AUTH'));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string)
            )
        );
        $result = curl_exec($ch);
        $obj = json_decode($result, true);        

        //學生禁止訪問
        if ($obj['success']) {

            if ($obj['kind'] == "學生") {
                return back()->withErrors(['errors' => ['學生禁止進入錯誤']]);
            }

            // 找出隸屬於哪一所學校 id 代號            
            $schools_id = config('ge.schools_id');
            $school_id = !isset($schools_id[$obj['code']]) ? 0 : $schools_id[$obj['code']];
                       
            $att['login'] = true;
            $att['name'] = $obj['name'];
            $att['school_code'] = $obj['code'];
            $att['school_name'] = $obj['school'];            
            $att['title'] = $obj['title'];            
            session(['gsuite_login' => $att]);            
            return redirect()->route('index');
        };        

        return back()->withErrors(['errors' => ['帳號密碼錯誤']]);
    }

    public function logout()
    {
        //Auth::logout();
        Session::flush();
        return redirect()->route('index');
    }

    public function upload_csv(Request $request)
    {
        $att = session('gsuite_login');
        if(!empty($att)){
            if(($att['name']=="王麒富" and $att['school_code']=="074628") or 
                ($att['name']=="林哲民" and $att['school_code']=="079998") or
                ($att['name']=="林金玉" and $att['school_code']=="079998") or
                ($att['name']=="林政言" and $att['school_code']=="079998"))                
            {
                $file = $request->file('csvFile');
                if ($file) {
                    $filename = "all.csv";
                    if(!is_dir(storage_path('app/privacy'))){
                        mkdir(storage_path('app/privacy'), 0777, true);
                    }
                    $file->storeAs('privacy',$filename);
                                
                    return redirect()->route('index');
                }
            }
        }        
        
    }

    public function schools($code=null)
    {
        $schools_id = [];
        $check_users = [];
        $teachers = [];
        $att = session('gsuite_login');
        if(!empty($att)){
            if(($att['name']=="王麒富" and $att['school_code']=="074628") or 
                ($att['name']=="林哲民" and $att['school_code']=="079998") or
                ($att['name']=="林金玉" and $att['school_code']=="079998") or
                ($att['name']=="林政言" and $att['school_code']=="079998"))
            {
                $schools_id = config('ge.schools_id');

                if($code){
                    $staffs = Staff::where('staff_sid', $code)   
                    ->where('staff_status', 1)    
                    ->where('staff_kind','<>', '學生')     
                    ->get();
                    foreach($staffs as $staff) {
                        $pid = mb_convert_kana($staff->staff_plan_id, 'as');
                        $pid = str_replace(' ', '', $pid);
                        $pid = strtoupper($pid);                                                    
                        $teachers[$pid]['name'] = $staff->staff_name;        
                        $teachers[$pid]['title'] = $staff->staff_title; 	                                       
                    }      
                    
                    //dd($teachers);
                    
                    if(is_file(storage_path('app/privacy/all.csv'))){
                        $csvFile = storage_path('app/privacy/all.csv');                    
                        if (($handle = fopen($csvFile, 'r')) !== false) {
                            while (($data = fgetcsv($handle, 1000, ',')) !== false) {                                                        
                                $pid = mb_convert_kana($data[1], 'as');
                                $pid = str_replace(' ', '', $pid);
                                $pid = strtoupper($pid);                                                                                                                                
                                
                                if(!empty($pid)){
                                $users[$pid]['date'] = $data[0];
                                $users[$pid]['pid'] = $pid;
                                $users[$pid]['gsuite'] = $data[3];
                                $users[$pid]['agree'] = $data[4];                            
                                $users[$pid]['mail'] = $data[2];      
                                }
                            }                    
                            fclose($handle);
                        } else {
                            echo "無法開啟檔案";
                        }                                            
                    }
                    
                    foreach($teachers as $k => $v) {
                        if(isset($users[$k])){
                            $check_users[$k]['date'] = $users[$k]['date'];                                                
                            $pid = $this->hideAccount($users[$k]['pid']);
                            $check_users[$k]['pid'] = $pid;
                            $check_users[$k]['agree'] = $users[$k]['agree'];
                            $check_users[$k]['mail'] = $users[$k]['mail'];
                            $v['name'] = $this->hideMiddleChineseName($v['name']);
                            $check_users[$k]['name'] = $v['name'];
                            $check_users[$k]['title'] = $v['title'];
                            $gsuite = str_replace('@chc.edu.tw', '', $users[$k]['gsuite']);
                            $gsuite = $this->hideAccount($gsuite);
                            $check_users[$k]['gsuite_account'] = $gsuite;
                        }
                    }                      
                }
            }        
        }
        $data = [
            'schools_id' => $schools_id,
            'code' => $code,
            'check_users' => $check_users,
            'school_name' => isset($schools_id[$code]) ? $schools_id[$code] : '',
        ];
        return view('schools',$data);
    }

    public function hideMiddleChineseName($name) {
        $len = mb_strlen($name, 'UTF-8');
    
        if ($len <= 2) {
            // 如果只有兩個字，就隱藏第二個
            return mb_substr($name, 0, 1, 'UTF-8') . '＊';
        }
    
        // 三個字以上，隱藏中間所有字
        $first = mb_substr($name, 0, 1, 'UTF-8');
        $last = mb_substr($name, -1, 1, 'UTF-8');
        $middleHidden = str_repeat('＊', $len - 2);
    
        return $first . $middleHidden . $last;
    }

    public function hideAccount($account) {
        $len = mb_strlen($account, 'UTF-8');

        if ($len <= 1) {
            // 帳號長度為 0 或 1，直接回傳
            return $account;
        } elseif ($len == 2) {
            // 長度為 2，只保留第一個
            return mb_substr($account, 0, 1, 'UTF-8') . '*';
        }
    
        $first = mb_substr($account, 0, 1, 'UTF-8');
        $last = mb_substr($account, -1, 1, 'UTF-8');
        $hidden = str_repeat('*', $len - 2);
    
        return $first . $hidden . $last;
    }

    public function isValidTaiwanID($id) {
        // 檢查基本格式
        if (!preg_match("/^[A-Z][1289][0-9]{8}$/", strtoupper($id))) {
            return false;
        }
    
        // 字母對應數值表（A=10, ..., Z=33，I=34, O=35）
        $letters = [
            'A'=>10, 'B'=>11, 'C'=>12, 'D'=>13, 'E'=>14, 'F'=>15, 'G'=>16, 'H'=>17,
            'I'=>34, 'J'=>18, 'K'=>19, 'L'=>20, 'M'=>21, 'N'=>22, 'O'=>35, 'P'=>23,
            'Q'=>24, 'R'=>25, 'S'=>26, 'T'=>27, 'U'=>28, 'V'=>29, 'W'=>32, 'X'=>30,
            'Y'=>31, 'Z'=>33
        ];
    
        $id = strtoupper($id);
        $letter = $id[0];
        if (!isset($letters[$letter])) return false;
    
        // 將英文字轉成兩位數
        $n1 = intval($letters[$letter] / 10);
        $n2 = $letters[$letter] % 10;
    
        $nums = str_split(substr($id, 1)); // 取後 9 碼為陣列
        $type = $id[1]; // 第二碼判斷身分類型
    
        // 權重計算
        $weights = [1, 9, 8, 7, 6, 5, 4, 3, 2, 1];
    
        // 計算總和（A 字母的兩位數 + 身分證數字）
        $sum = $n1 * $weights[0] + $n2 * $weights[1];
    
        for ($i = 0; $i < 8; $i++) {
            $sum += intval($nums[$i]) * $weights[$i + 2];
        }
    
        // 舊式居留證（第二碼為 8 或 9）需要將第 9 碼也乘權重 1
        if ($type === '8' || $type === '9') {
            $sum += intval($nums[8]);
        } else {
            // 本國與新式居留證，最後一碼是檢查碼，需加進總和中
            $sum += intval($nums[8]);
        }
    
        // 驗證結果
        return $sum % 10 === 0;
    }    

    public function containsFullWidth($string) {
        return preg_match('/[^\x00-\x7F]/u', $string);
    }
    
}
