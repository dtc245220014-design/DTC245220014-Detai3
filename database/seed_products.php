<?php
// seed_products.php - Part 1/2 (products 1-5). Chay: wp eval-file seed_products.php
$cta = '<div style="text-align: center; margin-top: 20px;"><a href="#contact" style="background-color: #0073aa; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">LIEN HE TU VAN & NHAN BAO GIA</a></div>';
function spec_table($rows) {
    $h = '<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; margin: 16px 0;"><thead><tr style="background-color: #0073aa; color: white;"><th style="text-align:left;">Thong so</th><th style="text-align:left;">Chi tiet</th></tr></thead><tbody>';
    $i = 0;
    foreach ($rows as $k => $v) {
        $bg = ($i % 2 == 0) ? ' style="background-color: #f2f7fb;"' : '';
        $h .= "<tr$bg><td><strong>$k</strong></td><td>$v</td></tr>";
        $i++;
    }
    return $h . '</tbody></table>';
}
$products = array(
array('Vot Cau Long Yonex Astrox 88D Pro - Danh cho tay vot thien cong manh me',
'Yonex Astrox 88D Pro - sieu pham tan cong cho tay vot phong trao va chuyen nghiep. Dau vot nang, than cung, ho tro dap cau uy luc va on dinh.',
'<p><strong>Yonex Astrox 88D Pro</strong> la cay vot thien cong (head-heavy) noi tieng cua Yonex, duoc nhieu tay vot don nam hang dau tin dung. Cong nghe Rotational Generator System giup ra luc nhanh, dap cau cam, cau di sau va chuan xac.</p><h3>Tinh nang noi bat</h3><ul><li>Dau vot nang, than sieu cung - toi da suc manh tan cong.</li><li>Khung isometric mo rong diem ngot.</li><li>Son nham cao cap, chong xuoc, ben mau.</li></ul>',
array('Thuong hieu'=>'Yonex (Nhat Ban)','Xuat xu'=>'Nhat Ban','Chat lieu'=>'HM Graphite + Tungsten','Trong luong'=>'4U (80-84g) / 3U (85-89g)','Do cung'=>'Extra Stiff','Bao hanh'=>'12 thang chinh hang','Gia tham khao'=>'4.290.000 VND')),
array('Tai Nghe Bluetooth Chong On Sony WH-1000XM5 - Am thanh Hi-Res chan thuc',
'Sony WH-1000XM5 - vua chong on voi 8 micro, am thanh Hi-Res, pin 30 gio. Lua chon hang dau cho cong viec va giai tri.',
'<p><strong>Sony WH-1000XM5</strong> dan dau phan khuc chong on voi chip QN1 + V1, 8 micro thu tieng on. Mang loa 30mm tai tao am Hi-Res chi tiet, bass sau, treble sach.</p><h3>Tinh nang noi bat</h3><ul><li>ANC manh nhat cua Sony.</li><li>Pin 30 gio, sac nhanh 3 phut nghe 3 gio.</li><li>4 micro dam thoai, ho tro multipoint.</li></ul>',
array('Thuong hieu'=>'Sony (Nhat Ban)','Xuat xu'=>'Malaysia','Cau hinh'=>'Driver 30mm, Bluetooth 5.2, LDAC/AAC','Trong luong'=>'250g','Pin'=>'30 gio (ANC bat)','Bao hanh'=>'12 thang chinh hang','Gia tham khao'=>'8.490.000 VND')),
array('Dong Ho Thong Minh Apple Watch Series 9 - Quan ly suc khoe & the thao',
'Apple Watch Series 9 chip S9, man hinh 2000 nits, Double Tap, do oxy mau, ECG, GPS. Tro ly suc khoe toan dien.',
'<p><strong>Apple Watch Series 9</strong> so huu chip S9, man hinh Retina luon bat sang gap doi. Cam bien nhip tim, SpO2, ECG, nhiet do giup theo doi suc khoe 24/7, hon 90 che do luyen tap.</p><h3>Tinh nang noi bat</h3><ul><li>Double Tap khong can cham man hinh.</li><li>GPS + khang nuoc 50m.</li><li>Theo doi giac ngu, luyen tap chuyen sau.</li></ul>',
array('Thuong hieu'=>'Apple (My)','Xuat xu'=>'Trung Quoc','Cau hinh'=>'Chip S9, man hinh 45mm Retina 2000 nits, GPS','Trong luong'=>'38.7g (ban 45mm)','Pin'=>'18 gio, sac nhanh','Bao hanh'=>'12 thang chinh hang','Gia tham khao'=>'10.990.000 VND')),
array('Vot Cau Long Victor Thruster Ryuga II - Suc manh dap cau uy luc',
'Victor Thruster Ryuga II - vot tan cong toan dien, than cung, dau nang, cho cu dap cau bua bo.',
'<p><strong>Victor Thruster Ryuga II</strong> duoc toi uu khi dong hoc khung vot, tang toc do vuot vot ma van on dinh. Cong nghe HARD CORED cho luc dap manh, cau cam, it rung.</p><h3>Tinh nang noi bat</h3><ul><li>Dau vot nang, tan cong lien tuc.</li><li>Khung kiem chong can gio.</li><li>Phu hop trinh do trung binh - kha.</li></ul>',
array('Thuong hieu'=>'Victor (Dai Loan)','Xuat xu'=>'Trung Quoc','Chat lieu'=>'High Modulus Graphite + HARD CORED','Trong luong'=>'4U / 3U','Do cung'=>'Stiff','Bao hanh'=>'12 thang chinh hang','Gia tham khao'=>'3.850.000 VND')),
array('Ban Phim Co Khong Day Keychron K2 Pro - Switch Gateron G Pro go muot ma',
'Keychron K2 Pro layout 75%, switch Gateron G Pro, QMK/VIA, 3 che do ket noi. Ban phim quoc dan cho lap trinh vien.',
'<p><strong>Keychron K2 Pro</strong> layout 75% gon nhe, switch Gateron G Pro go muot. Ho tro QMK/VIA remap, keycap PBT double-shot, ket noi Bluetooth 5.1 / 2.4G / USB-C.</p><h3>Tinh nang noi bat</h3><ul><li>Hot-swap thay switch khong han.</li><li>Pin 4000mAh, dung 100+ gio.</li><li>Tuong thich Windows / macOS / Linux.</li></ul>',
array('Thuong hieu'=>'Keychron (Hong Kong)','Xuat xu'=>'Trung Quoc','Cau hinh'=>'Layout 75%, Gateron G Pro, QMK/VIA, BT/2.4G/USB-C','Trong luong'=>'~1000g','Pin'=>'4000mAh','Bao hanh'=>'12 thang','Gia tham khao'=>'2.390.000 VND')),
array('Chuot Logitech MX Master 3S - Toi uu hieu suat cong viec',
'Logitech MX Master 3S - chuot cong thai hoc 8K DPI, click em, cuon MagSpeed. Vu khi nang suat cho dan van phong.',
'<p><strong>Logitech MX Master 3S</strong> thiet ke cho nguoi lam viec 8+ gio/ngay: form cong thai hoc, cam bien 8K DPI, Quiet Click giam 90% tieng on, cuon MagSpeed 1000 dong/giay.</p><h3>Tinh nang noi bat</h3><ul><li>Ket noi 3 thiet bi.</li><li>Pin 70 ngay, sac nhanh USB-C.</li><li>Nut tuy bien cho Excel, VS Code.</li></ul>',
array('Thuong hieu'=>'Logitech (Thuy Si)','Xuat xu'=>'Trung Quoc','Cau hinh'=>'8K DPI, Bluetooth + Logi Bolt, USB-C','Trong luong'=>'141g','Pin'=>'70 ngay','Bao hanh'=>'12 thang chinh hang','Gia tham khao'=>'2.590.000 VND')),
array('Loa Bluetooth JBL Charge 5 - Khang nuoc IP67, bass manh me',
'JBL Charge 5 - loa di dong pin 20 gio, IP67, sac nguoc cho dien thoai. Bung no moi cuoc vui ngoai troi.',
'<p><strong>JBL Charge 5</strong> mang chat am JBL Original Pro Sound, bass sau nho woofer racetrack + 2 passive radiator. IP67 chong nuoc bui; pin 20 gio, sac du phong cho dien thoai.</p><h3>Tinh nang noi bat</h3><ul><li>PartyBoost mo rong stereo.</li><li>Vai du bo ven, chong soc.</li><li>Sac day 4 gio, cong USB sac nguoc.</li></ul>',
array('Thuong hieu'=>'JBL by Harman (My)','Xuat xu'=>'Trung Quoc','Cau hinh'=>'Bluetooth 5.1, 40W RMS, IP67','Trong luong'=>'960g','Pin'=>'20 gio','Bao hanh'=>'12 thang chinh hang','Gia tham khao'=>'3.990.000 VND')),
array('Vot Cau Long Yonex Arcsaber 11 Pro - Kiem soat cau chinh xac tuyet doi',
'Yonex Arcsaber 11 Pro - huyen thoai kiem soat, can bang cong thu, chinh xac den tung milimet.',
'<p><strong>Yonex Arcsaber 11 Pro</strong> ke thua dong Arcsaber huyen thoai. Cong nghe CONTROL-ASSIST BUMPER cai thien do giu cau, cho pha dieu cau, phong thu chinh xac.</p><h3>Tinh nang noi bat</h3><ul><li>Can bang cong - thu, loi danh ky thuat.</li><li>Than cung trung binh, de dieu khien.</li><li>Khung coupe tang do ben.</li></ul>',
array('Thuong hieu'=>'Yonex (Nhat Ban)','Xuat xu'=>'Nhat Ban','Chat lieu'=>'HM Graphite + POCKETING BOOSTER','Trong luong'=>'4U / 3U','Do cung'=>'Stiff','Bao hanh'=>'12 thang chinh hang','Gia tham khao'=>'4.090.000 VND')),
array('Giay Chay Bo Nike Air Zoom Pegasus 40 - Em ai & toi uu chuyen dong',
'Nike Pegasus 40 - giay chay quoc dan, dem Zoom Air + React, om chan, em ai tu 5K den marathon.',
'<p><strong>Nike Air Zoom Pegasus 40</strong> dem Zoom Air + foam React sieu nay. Than engineered mesh thoang khi, got chac, de truot thap giam chan thuong.</p><h3>Tinh nang noi bat</h3><ul><li>Nhe, chay hang ngay.</li><li>De cao su chong mon, bam duong.</li><li>Nhieu phoi mau thoi trang.</li></ul>',
array('Thuong hieu'=>'Nike (My)','Xuat xu'=>'Viet Nam','Chat lieu'=>'Engineered mesh + Zoom Air + React','Trong luong'=>'~290g (size 42)','Size'=>'39 - 45','Bao hanh'=>'30 ngay doi size','Gia tham khao'=>'3.490.000 VND')),
array('Man Hinh Dell UltraSharp U2723QE 4K IPS - Mau sac chuan xac',
'Dell U2723QE 27 inch 4K IPS Black, 98% DCI-P3, USB-C 90W. Man hinh chuan studio cho designer, lap trinh.',
'<p><strong>Dell UltraSharp U2723QE</strong> tam nen IPS Black, tuong phan gap doi IPS thuong, 4K sac net. Phu mau 98% DCI-P3, Delta-E &lt; 2, USB-C 90W + Hub RJ45/USB.</p><h3>Tinh nang noi bat</h3><ul><li>ComfortView Plus, 60Hz.</li><li>Chan xoay doc, nang ha linh hoat.</li><li>Daisy-chain 2 man hinh 4K.</li></ul>',
array('Thuong hieu'=>'Dell (My)','Xuat xu'=>'Trung Quoc','Cau hinh'=>'27 inch 4K IPS Black, 98% DCI-P3, USB-C 90W','Trong luong'=>'6.64kg (kem chan)','Tan so'=>'60Hz, 5ms','Bao hanh'=>'36 thang tan nha','Gia tham khao'=>'15.990.000 VND')),
);
$cats = array(get_cat_ID('San Pham Noi Bat'), get_cat_ID('Trung Bay San Pham'));
$ok = 0;
foreach ($products as $p) {
    $content = $p[2] . '<h3>Thong so ky thuat</h3>' . spec_table($p[3]) . $cta;
    $id = wp_insert_post(array('post_title'=>$p[0],'post_excerpt'=>$p[1],'post_content'=>$content,'post_status'=>'publish','post_type'=>'post','post_category'=>$cats));
    if ($id && !is_wp_error($id)) { $ok++; echo "OK $id: {$p[0]}\n"; }
    else { echo "FAIL: {$p[0]}\n"; }
}
echo "TOTAL_CREATED=$ok\n";
