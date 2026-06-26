<?php 
// PHP code to extract IP  
  
function getVisIpAddr() { 
    return '185.225.28.204';      
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) { 
        return $_SERVER['HTTP_CLIENT_IP']; 
    } 
    else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { 
        return $_SERVER['HTTP_X_FORWARDED_FOR']; 
    } 
    else { 
        return $_SERVER['REMOTE_ADDR']; 
    } 
} 
  
// Store the IP address 
$vis_ip = getVisIPAddr(); 
  
// Display the IP address 
echo $vis_ip; 
   
// PHP code to obtain country, city,  
// continent, etc using IP Address 
  
$ip = '52.25.109.230'; 
$ip = '130.237.28.40'; 
$ip = '185.225.28.204';
$ip = '151.101.131.5';

$ips =
     "13.48.249.95
16.16.213.35
18.119.14.3
181.214.142.2
192.175.111.228
192.175.111.236
192.175.111.239
192.175.111.242
192.175.111.245
192.175.111.247
192.175.111.251
192.175.111.253
205.169.39.210
205.169.39.243
23.178.112.203
23.178.112.205
23.178.112.206
3.145.144.110
34.123.170.104
34.172.169.244
34.72.176.129
35.92.150.46
54.218.68.159
64.15.129.106
64.15.129.107
64.15.129.113
64.15.129.114
64.15.129.123
64.15.129.125
65.154.226.166
65.154.226.167
65.154.226.170
164.92.216.227
167.71.82.17
185.176.246.72
";
$ips = "
104.166.80.100
104.166.80.132
104.166.80.158
104.166.80.181
104.166.80.70
149.154.161.245
154.38.188.70
161.35.0.50
164.92.216.227
167.71.82.17
178.62.9.18
18.203.232.17
181.214.142.2
185.176.246.72
185.214.97.147
185.225.28.204
192.0.86.161
192.0.89.219
192.81.57.222
194.103.157.93
194.230.146.126
195.201.114.80
20.93.43.179
205.169.39.14
205.169.39.22
205.169.39.29
205.169.39.7
31.220.103.142
31.220.103.143
31.220.103.144
31.220.103.145
31.220.103.146
31.220.103.147
31.220.97.151
31.220.97.154
31.220.97.163
31.220.97.173
31.220.98.151
34.242.132.179
39.105.120.190
44.234.23.197
5.161.124.144
5.161.86.62
62.146.227.78
62.146.227.79
62.146.227.80
64.227.166.132
64.23.145.119
66.102.9.38
66.249.64.102
66.249.81.34
66.249.89.168
68.183.112.131
93.158.130.173
";
$ips="
194.103.157.93
";
foreach (preg_split("/\n/", $ips) as $ip) {
    $ip = trim($ip);
    if (empty($ip)) continue;
    $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=$ip"));
    
    echo "\n$ip\n";
    echo 'Country Name: ' . $ipdat->geoplugin_countryName . "\n"; 
    //    echo 'Country Code: ' . $ipdat->geoplugin_countryCode . "\n"; 
    if ($c=$ipdat->geoplugin_city) echo "City Name: $c\n";
    //echo 'Continent Name: ' . $ipdat->geoplugin_continentName . "\n"; 
    //echo 'Latitude: ' . $ipdat->geoplugin_latitude . "\n"; 
    //echo 'Longitude: ' . $ipdat->geoplugin_longitude . "\n"; 
    //echo 'Currency Symbol: ' . $ipdat->geoplugin_currencySymbol . "\n"; 
    //echo 'Currency Code: ' . $ipdat->geoplugin_currencyCode . "\n"; 
    echo 'Timezone: ' . $ipdat->geoplugin_timezone . "\n"; 
}

//var_dump($ipdat);
