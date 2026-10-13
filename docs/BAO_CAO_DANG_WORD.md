# BÁO CÁO ĐÁN GIÁ MÔN HỌC — DỮ LIỆU NGUỒN CHO MICROSOFT WORD

> **Hướng dẫn sử dụng file:** Đây là bản soạn thảo thô (Markdown) để copy vào
> Microsoft Word (.docx). Khi xuất sang Word: đặt phông chữ Times New Roman 13pt
> (tiêu đề 14pt, đậm), giãn dòng 1.5, lề trái/phải 3cm, lề trên 2cm, lề dưới 2cm;
> đánh số trang tự động; chuyển các bảng Markdown thành bảng Word (Insert → Table).

---

# TRANG BÌA

<center>

**TRƯỜNG ĐẠI HỌC CÔNG NGHỆ THÔNG TIN VÀ TRUYỀN THÔNG**
**ĐẠI HỌC THÁI NGUYÊN**

**KHOA CÔNG NGHỆ THÔNG TIN**

**——————————————————————————————————————**

# BÁO CÁO ĐÁN GIÁ MÔN HỌC

**Tên môn học:** Triển khai và Quản trị Hệ thống Phần mềm (Mã HP: DTC245220014)

**Tên đề tài:** Đề tài 3 — Triển khai Website Quảng bá Sản phẩm WordPress bằng
Docker Compose tích hợp Hệ thống Giám sát & Quản lý Log tập trung.

|  |  |
|---|---|
| **Lớp:** | K23A — An toàn thông tin |
| **Họ và tên sinh viên:** | Nguyễn Minh Đức |
| **Giảng viên hướng dẫn:** | ........................................ |
| **Năm học:** | 2026 — 2027 |

</center>

---

## MỤC LỤC

| STT | Nội dung |
|---|---|
| — | Trang bìa · Bảng Tóm tắt báo cáo (Executive Summary) |
| **Chương 1** | Giới thiệu và Tổng quan đề tài |
| 1.1 | Đặt vấn đề và mục tiêu triển khai hệ thống WordPress sản phẩm |
| 1.2 | Tổng quan các công nghệ sử dụng |
| **Chương 2** | Thiết kế Architecture và mạng nội bộ dự án |
| 2.1 | Sơ đồ kiến trúc tổng thể 10 services |
| 2.2 | Thiết kế mạng phân tách (Network Isolation) |
| 2.3 | Cấu hình Volume và lưu trữ dữ liệu bền vững |
| **Chương 3** | Quy trình triển khai và chi tiết các mốc Commit Git |
| 3.1 | Bảng tổng hợp lịch sử Git Commits |
| 3.2 – 3.6 | Chi tiết Commit 1 → Commit 5 |
| **Chương 4** | Hệ thống giám sát hiệu năng và quản lý log tập trung |
| 4.1 | Giám sát Prometheus & cAdvisor/Node Exporter (PromQL) |
| 4.2 | Xây dựng Grafana Dashboard |
| 4.3 | Quản lý log tập trung Loki & Promtail (LogQL) |
| **Chương 5** | Đánh giá bảo mật Hardening hệ thống |
| 5.1 | Bảng tổng hợp 5 giải pháp Hardening |
| 5.2 | Đánh giá tính an toàn thực tế |
| **Chương 6** | Troubleshooting & Kết luận |
| 6.1 | Bảng tổng hợp lỗi thực tế và cách xử lý |
| 6.2 | Kết luận và hướng phát triển |

---

## TÓM TẮT BÁO CÁO (EXECUTIVE SUMMARY)

Báo cáo trình bày toàn bộ quy trình thiết kế, triển khai và đánh giá hệ thống
**Website Quảng bá Sản phẩm WordPress** chạy trọn vẹn trên **Docker Compose** với
**10 services**, **2 network cách ly**, được bổ sung **hệ thống giám sát hiệu năng**
(Prometheus, cAdvisor, Node-Exporter, Grafana) và **hệ thống quản lý log tập trung**
(Loki, Promtail), đồng thời áp dụng **5 nhóm biện pháp Hardening bảo mật**.

Kết quả kiểm chứng thực tế: toàn bộ **10/10 container khởi động ổn định**
(mysql & cadvisor ở trạng thái healthy); website `https://localhost` phản hồi
`302` tới trang cài đặt WordPress; phpMyAdmin `200`; chuyển hướng HTTP→HTTPS
`301`; Prometheus thu **3/3 target UP** với scrape interval 5 giây; Grafana
`10.2.2` kết nối thành công datasource; Loki trả về log thời gian thực qua 3 câu
LogQL; kết nối TLS thực tế **TLSv1.3 — TLS_AES_256_GCM_SHA384**, TLS 1.0 bị từ
chối; toàn bộ mật khẩu và private key **không từng được commit lên GitHub**.

Dự án được quản lý bằng Git với **5 mốc commit** (5 giai đoạn), mỗi giai đoạn
tương ứng một chương của báo cáo, tổng số hơn **1.000 dòng cấu hình** được kiểm
chứng bằng lệnh trên môi trường thực tế.

---

# CHƯƠNG 1: GIỚI THIỆU VÀ TỔNG QUAN ĐỀ TÀI

## 1.1. Đặt vấn đề và mục tiêu triển khai hệ thống WordPress sản phẩm

Trong bối cảnh thương mại điện tử và marketing số, việc sở hữu một **website quảng
bá sản phẩm** hoạt động ổn định, bảo mật và có khả năng quan sát (observability)
là yêu cầu cơ bản của mọi doanh nghiệp. Website WordPress là lựa chọn phổ biến
nhờ hệ sinh thái plugin phong phú, tuy nhiên khi đưa vào vận hành thật, quản trị
hệ thống phải giải quyết đồng thời nhiều bài toán: **phân luồng truy cập** bằng
reverse proxy, **bảo mật kênh truyền** bằng HTTPS, **cô lập cơ sở dữ liệu** để
giảm bề mặt tấn công, **giám sát tài nguyên** để phát hiện điểm nghẽn và **tập
trung log** để truy vết sự cố.

Đề tài được triển khai với các mục tiêu cụ thể:

1. **Mục tiêu cốt lõi:** Dựng website quảng bá sản phẩm WordPress chạy trọn vẹn
   trong container Docker bằng Docker Compose, phục vụ qua Nginx Reverse Proxy
   với chứng chỉ SSL tự ký (HTTPS).
2. **Mục tiêu vận hành:** Áp dụng mô hình cấu hình chuẩn (declarative
   infrastructure) — toàn bộ hệ thống được mô tả trong một file
   `docker-compose.yml`, có thể khởi tạo lại trên máy bất kỳ bằng một lệnh duy nhất.
3. **Mục tiêu giám sát:** Tích hợp bộ ba Prometheus + cAdvisor + Node-Exporter để
   thu thập số liệu từ 3 cấp độ (container, máy host, ứng dụng) và trực quan hóa
   bằng Grafana.
4. **Mục tiêu quản lý log:** Xây dựng pipeline log tập trung Loki – Promtail, tự
   động thu thập stdout của mọi container và truy vấn bằng ngôn ngữ LogQL.
5. **Mục tiêu bảo mật:** Áp dụng tối thiểu 5 nhóm biện pháp Hardening (cô lập
   mạng, hạn chế đặc quyền, giới hạn tài nguyên, tăng cường TLS/Headers, quản lý
   bí mật) và chứng minh bằng lệnh kiểm tra thực tế.
6. **Mục tiêu quản trị nguồn:** Toàn bộ quá trình triển khai được ghi nhận qua
   hệ thống commit Git theo từng giai đoạn, thuận tiện cho đánh giá và khôi phục.

## 1.2. Tổng quan các công nghệ sử dụng trong bài tập lớn

| STT | Công nghệ | Phiên bản | Vai trò trong hệ thống |
|---|---|---|---|
| 1 | **WordPress** | 6.x (image `wordpress:latest`) | Nền tảng CMS đăng bài viết, hình ảnh quảng bá sản phẩm |
| 2 | **MySQL** | 8.0 | Cơ sở dữ liệu lưu bài viết, cấu hình WordPress |
| 3 | **Nginx SSL Reverse Proxy** | 1.25-alpine | Nhận request :80/:443, TLS termination, Security Headers, proxy sang WordPress/phpMyAdmin |
| 4 | **phpMyAdmin** | 5.2 | Giao diện web quản trị MySQL (qua sub-path `/phpmyadmin/`) |
| 5 | **Docker Compose** | v5.x (Compose Spec) | Định nghĩa & điều phối 10 services, 2 networks, 5 volumes |
| 6 | **Prometheus** | v2.48.0 | Cơ sở dữ liệu metrics, scrape mỗi 5 giây, thực thi PromQL |
| 7 | **Grafana** | 10.2.2 | Dashboard giám sát, Explore cho PromQL và LogQL |
| 8 | **cAdvisor** | v0.60.5 (ghcr.io) | Thu metrics từng container: CPU, RAM, Network, I/O |
| 9 | **Node-Exporter** | v1.7.0 | Thu metrics máy host: CPU, RAM, ổ đĩa, mạng |
| 10 | **Loki** | 2.9.2 | Kho lưu trữ log, truy vấn LogQL, lưu filesystem |
| 11 | **Promtail** | 2.9.2 | Đọc `/var/lib/docker/containers`, gán nhãn, đẩy log về Loki |

**Bảng 1.1.** Danh mục công nghệ của bài tập lớn.

---

# CHƯƠNG 2: THIẾT KẾ ARCHITECTURE VÀ MẠNG NỘI BỘ DỰ ÁN

## 2.1. Sơ đồ kiến trúc tổng thể 10 services

Hệ thống gồm 10 container được chia thành 4 tầng chức năng: tầng phục vụ web,
tầng dữ liệu, tầng giám sát metrics và tầng quản lý log.

```text
                         NGƯỜI DÙNG / TRÌNH DUYỆT
                     http://localhost  |  https://localhost
                                       |  :80 / :443
                                       v
      ======================  FRONTEND_NET (bridge)  ======================
      |                                                                  |
      |   +-------------------+      +--------------------------------+   |
      |   |  NGINX 1.25       |      |  TẦNG GIÁM SÁT METRICS        |   |
      |   |  Reverse Proxy    |      |  +------------+  +----------+  |   |
      |   |  SSL + Headers    |      |  | PROMETHEUS |<-| cAdvisor |  |   |
      |   |  :80 / :443       |      |  |   :9090    |  |  :8080   |  |   |
      |   +----+--------+-----+      |  | scrape 5s  |  +----------+  |   |
      |        |        |            |  +-----+------+                |   |
      |        |        |            |        ^     +-----------+     |   |
      |        |        |            |        |     |Node-Exp.  |     |   |
      |        |        |            |  +-----+--+  |  :9100    |     |   |
      |        |        |            |  | GRAFANA |  +-----------+     |   |
      |        |        |            |  |  :3000  |                    |   |
      |        |        |            +--+---------+--------------------+   |
      |        |        |                             |  Explore / Dash    |
      |        v        v                             v                    |
      |  +-----------+ +--------------+   +--------------------+           |
      |  | WORDPRESS | |  PHPMYADMIN  |   | TẦNG LOG           |           |
      |  |   :80     | |     :80      |   | +------+ +--------+|           |
      |  +-----+-----+ +------+-------+   | | LOKI |<|PROMTAIL||           |
      |        |                |         | |:3100 | +--------+|           |
      ====================================================================
                     |                                  ^
                     |  BACKEND_NET (internal: true)     | doc file log
                     |                                  | /var/lib/docker/
                     v                                  |  containers (RO)
            +------------------+                         |
            |   MySQL 8.0      |   <----- stdout --------+
            |     :3306        |
            | KHÔNG EXPOSE PORT|
            +------------------+
```

**Mô tả hoạt động:** (1) Nginx nhận toàn bộ request HTTP/HTTPS, redirect 80→443,
kiểm tra TLS và gắn Security Headers, sau đó `proxy_pass` sang
`wordpress:80` hoặc `phpmyadmin:80`; (2) WordPress ghi dữ liệu xuống MySQL qua
 kênh nội bộ `backend_net`; (3) Prometheus scrape cAdvisor (metrics container)
 và Node-Exporter (metrics host) mỗi 5 giây; (4) Promtail đọc file log JSON
 của mọi container và đẩy về Loki; người dùng tra cứu qua Grafana.

## 2.2. Thiết kế mạng phân tách (Frontend Network public & Backend Network internal)

Triết lý thiết kế áp dụng nguyên tắc **zero-standing-privilege ở tầng mạng**:

- **`frontend_net`** (driver `bridge`): chứa các service cần giao tiếp với người
  dùng hoặc với nhau — Nginx, WordPress, phpMyAdmin, Prometheus, Grafana, Loki,
  cAdvisor, Node-Exporter, Promtail.
- **`backend_net`** (driver `bridge`, **`internal: true`**): chứa MySQL và các
  service cần truy vấn DB (WordPress, phpMyAdmin). Thuộc tính `internal: true`
  khiến container trong mạng này **không có route ra Internet** — MySQL không thể
  bị lợi dụng để tải payload từ bên ngoài, cũng **không khai báo `ports`** nên
  không có cổng nào của DB được publish ra host.

Cấu hình khai báo trong `docker-compose.yml`:

```yaml
networks:
  frontend_net:
    driver: bridge
  backend_net:
    driver: bridge
    internal: true   # backend_net không có truy cập Internet bên ngoài
```

**Bảng 2.1.** Phân bổ mạng của từng service:

| Service | frontend_net | backend_net | Ghi chú |
|---|---|---|---|
| db (MySQL) | — | Có | Chỉ nội bộ, không port, không Internet |
| wordpress | Có | Có | Cầu nối web ↔ database |
| phpmyadmin | Có | Có | Cầu nối web ↔ database |
| nginx | Có | — | Điểm vào duy nhất của hệ thống |
| prometheus / grafana / loki / cadvisor / node-exporter / promtail | Có | — | Tầng observability |

## 2.3. Cấu hình Volume và lưu trữ dữ liệu bền vững (Persistence Data)

| Volume | Service gắn | Đường dẫn trong container | Dữ liệu lưu trữ |
|---|---|---|---|
| `db_data` | db | `/var/lib/mysql` | Toàn bộ dữ liệu MySQL (bài viết WordPress) |
| `grafana_data` | grafana | `/var/lib/grafana` | Dashboard, datasource, cấu hình người dùng |
| `prometheus_data` | prometheus | `/prometheus` | Chuỗi thời gian metrics (TSDB) |
| `loki_data` | loki | `/loki` | Chunks + index log (boltdb-shipper) |
| `nginx_logs` | nginx | `/var/log/nginx` | Access/error log của Nginx |

Ngoài ra, 3 mount ở mức file/cầu:

- `./nginx/default.conf` → `/etc/nginx/conf.d/default.conf` (read-only): cấu hình reverse proxy.
- `./nginx/ssl` → `/etc/nginx/ssl` (read-only): chứng chỉ và private key SSL.
- `./promtail/positions.yaml` → `/tmp/positions.yaml`: **vị trí đọc log được persist**,
  tránh replay log cũ khi container bị recreate.

Với volume đặt theo tên (named volume), dữ liệu **sống sót qua `docker compose down`**
và chỉ bị xóa khi chạy `docker compose down -v` — đảm bảo tính bền vững cho dữ liệu
kinh doanh (bài viết, ảnh sản phẩm).

---

# CHƯƠNG 3: QUY TRÌNH TRIỂN KHAI VÀ CHI TIẾT CÁC MỐC COMMIT GIT

## 3.1. Bảng tổng hợp lịch sử Git Commits

Toàn bộ dự án được quản lý trên GitHub `dtc245220014-design/DTC245220014-Detai3`,
nhánh `main`, mỗi giai đoạn phát triển ứng với một commit có message chuẩn.

| Mốc | Hash | Message | Nội dung chính | Files |
|---|---|---|---|---|
| Commit 1 | `9c44a0e` | `Commit 1: Deploy wordpress app with Nginx reverse proxy and HTTPS` | Core app: WordPress + MySQL + phpMyAdmin + Nginx SSL | 4 files, +313 |
| Commit 2 | `70cecad` | `Commit 2: Add Prometheus metrics and Grafana monitoring` | Prometheus + cAdvisor + Node-Exporter + Grafana | 3 files, +199/−5 |
| Commit 3 | `995752e` | `Commit 3: Add centralized logging with Loki and Promtail` | Loki + Promtail + cấu hình LogQL | 4 files, +236/−2 |
| Commit 4 | `0db29ca` | `Commit 4: Apply security hardening with resource limits and TLS hardening` | NNP, cap_drop, mem_limit/cpus, cipher TLS | 5 files, +158/−6 |
| Commit 5 | `030d714` | `Commit 5: Rewrite README with full project documentation` | Tài liệu hoá README 8 mục | 1 file, +358/−205 |

**Bảng 3.1.** Lịch sử 5 mốc commit trên nhánh `main`.

## 3.2. Detail Commit 1 — Core App WordPress + Nginx SSL Reverse Proxy & MySQL

- **Hash:** `9c44a0eda06b28b7b3fb8ad956024e63ba631717`
- **Message:** `Commit 1: Deploy wordpress app with Nginx reverse proxy and HTTPS`
- **Lệnh thực thi:**

```powershell
git add .
git commit -m "Commit 1: Deploy wordpress app with Nginx reverse proxy and HTTPS"
git push -u origin main
```

- **Nội dung:** khởi tạo `.gitignore` (chặn `.env`, `db_data/`, `nginx/ssl/`, log),
  file `.env` chứa mật khẩu mạnh cho MySQL root/user và cấu hình WordPress
  (`MYSQL_ROOT_PASSWORD=R00t@DTC245_2026#Strong!`...), `docker-compose.yml` với
  4 service đầu (`db`, `wordpress`, `phpmyadmin`, `nginx`) cùng 2 network
  `frontend_net`/`backend_net`, healthcheck MySQL (`mysqladmin ping`, chờ
  `service_healthy` trước khi khởi động WordPress), và `nginx/default.conf` với
  cấu hình reverse proxy `proxy_pass http://wordpress:80`, sub-path
  `/phpmyadmin/`, redirect 301 80→443, `server_tokens off` và bộ Security Headers.
- **Sinh chứng chỉ SSL tự ký** (thực hiện bằng Docker vì máy không có openssl host):

```powershell
docker run --rm -v "${PWD}/nginx/ssl:/ssl" alpine/openssl req -x509 -nodes -days 365 `
  -newkey rsa:2048 -keyout /ssl/nginx.key -out /ssl/nginx.crt `
  -subj "/C=VN/ST=HaNoi/L=HaNoi/O=NguyenMinhDuc_K23A_ATTT/CN=localhost"
```

- **Kết quả kiểm chứng:**

| Kiểm tra | Lệnh | Kết quả |
|---|---|---|
| Cú pháp Nginx | `docker compose exec nginx nginx -t` | `syntax is ok / test is successful` |
| Website HTTPS | `curl -sk https://localhost/` | `302` → `/wp-admin/install.php` |
| phpMyAdmin | `curl -sk https://localhost/phpmyadmin/` | `200` |
| Chuyển hướng HTTP | `curl -s http://localhost/` | `301` → `https://localhost/` |
| Security Headers | `curl -skI https://localhost/` | `Server: nginx`, X-Frame-Options, CSP... |

## 3.3. Detail Commit 2 — Metrics Monitoring Prometheus + Grafana + cAdvisor + Node Exporter

- **Hash:** `70cecaddbde56bfb97b1692f452ffc7999bddae8`
- **Message:** `Commit 2: Add Prometheus metrics and Grafana monitoring`
- **Lệnh thực thi:**

```powershell
docker compose up -d
git add .
git commit -m "Commit 2: Add Prometheus metrics and Grafana monitoring"
git push
```

- **Nội dung:** bổ sung 4 service — `prometheus` (v2.48.0, publish 9090, mount
  `prometheus/prometheus.yml`, volume `prometheus_data`), `cadvisor` (thu metrics
  container qua `/var/run/docker.sock` + rootfs read-only), `node-exporter`
  (v1.7.0, publish 9100, `--path.rootfs=/host`, `pid: host`), `grafana`
  (10.2.2, publish 3000, volume `grafana_data`, admin/admin). File
  `prometheus.yml` cấu hình `scrape_interval: 5s` với 3 scrape_configs:
  `prometheus` (localhost:9090), `cadvisor` (cadvisor:8080), `node-exporter`
  (node-exporter:9100).
- **Ghi chú kỹ thuật:** đề bài nêu `gcr.io/cadvisor/cadvisor:v0.47.0` nhưng Docker
  Desktop 29 dùng containerd-snapshotter gây ra lỗi `failed to identify the read-write
  layer ID` (google/cadvisor#3643, fix ở PR #3709 từ v0.54.0) → dự án dùng
  `ghcr.io/google/cadvisor:v0.60.5` để dashboard có số liệu thật.
- **Kết quả kiểm chứng:**

| Kiểm tra | Kết quả |
|---|---|
| Prometheus Targets | `prometheus=up`, `cadvisor=up`, `node-exporter=up` (3/3) |
| Grafana health | `{"database":"ok","version":"10.2.2"}` |
| Số liệu thật | `MEM dtc3_mysql = 362.1 MB`, `dtc3_wordpress = 154.5 MB`, `dtc3_nginx = 13 MB` |
| CPU host | `HOST_CPU_USAGE% = 3.43` |
| cAdvisor discovery | 13 container được track, đủ label `name`, `container_label_com_docker_compose_*` |

## 3.4. Detail Commit 3 — Centralized Logging Loki + Promtail

- **Hash:** `995752e4c84d08d03a81197ce554ff02af9ccacf`
- **Message:** `Commit 3: Add centralized logging with Loki and Promtail`
- **Lệnh thực thi:**

```powershell
docker compose up -d
git add .
git commit -m "Commit 3: Add centralized logging with Loki and Promtail"
git push
```

- **Nội dung:** thêm service `loki` (grafana/loki:2.9.2, publish 3100, mount
  `loki/loki-config.yml`, volume `loki_data`) và `promtail`
  (grafana/promtail:2.9.2, mount read-only `/var/lib/docker/containers` +
  `/var/run/docker.sock` + `promtail/promtail-config.yml`).
  - `loki-config.yml`: chế độ standalone, `auth_enabled: false`, storage
    filesystem (chunks/rules), index `boltdb-shipper` schema v11, compactor bật
    retention 168h.
  - `promtail-config.yml`: `docker_sd_configs` tự phát hiện container qua
    docker.sock (refresh 5s); **relabel tự gán nhãn `container_name`** (bỏ dấu
    `/` và bỏ prefix `dtc3_` → `dtc3_nginx` thành `nginx`), thêm
    `container_full_name`, `compose_service`, `compose_project`, `__path__`;
    pipeline `docker` parse JSON + stage `drop: older_than: 1h` loại entry cũ
    vượt cửa sổ out-of-order của Loki; positions được mount persist ra host.
- **Kết quả kiểm chứng:**

| Kiểm tra | Kết quả |
|---|---|
| Promtail targets | `added Docker target` cho 13 container, `finished transferring logs`, không lỗi push |
| Labels tại Loki | `compose_project, compose_service, container_full_name, container_name` |
| Giá trị `container_name` | `nginx, wordpress, mysql, phpmyadmin, prometheus, grafana, loki...` |
| Log Nginx thực tế | `172.30.0.1 - - [13/Oct/2026:04:08:41 +0000] "GET / HTTP/1.1" 302 0 "-" "curl/8.21.0"` |
| Grafana → Loki | `wget http://loki:3100/ready` từ container Grafana → `ready` |

## 3.5. Detail Commit 4 — Security Hardening (giới hạn tài nguyên, TLS hardening)

- **Hash:** `0db29ca`
- **Message:** `Commit 4: Apply security hardening with resource limits and TLS hardening`
- **Lệnh thực thi:**

```powershell
git add .
git commit -m "Commit 4: Apply security hardening with resource limits and TLS hardening"
git push
```

- **Nội dung:** (1) `security_opt: ["no-new-privileges:true"]` cho cả 10 service;
  (2) `cap_drop: ["ALL"]` cho Prometheus và Grafana (đều chạy non-root);
  (3) `mem_limit` + `cpus` từng container — MySQL `1g/1.0`, WordPress `512m/1.0`,
  phpMyAdmin/Nginx/Node-Exporter/Promtail `256m/0.5`, Prometheus/cAdvisor/Grafana/
  Loki `512m/0.5`; (4) Nginx nâng cipher lên whitelist `ECDHE-…GCM/CHACHA20:
  !aNULL:!MD5:!RC4:!3DES` và `ssl_session_tickets off`; (5) `.gitignore` bổ sung
  `promtail/positions.yaml`.
- **Kết quả kiểm chứng:** 10/10 container `SecurityOpt=["no-new-privileges:true"]`;
  `docker stats` toàn bộ dưới limit (`dtc3_mysql 363.6MiB/1GiB`);
  `openssl s_client` thực tế **TLSv1.3 — TLS_AES_256_GCM_SHA384**; kết nối `-tls1`
  bị handshake failure; toàn bộ endpoint vẫn `200/302` sau khi áp dụng.

## 3.6. Detail Commit 5 — Tài liệu hoá hệ thống README.md

- **Hash:** `030d714`
- **Message:** `Commit 5: Rewrite README with full project documentation`
- **Lệnh thực thi:**

```powershell
git add .
git commit -m "Commit 5: Rewrite README with full project documentation"
git push
```

- **Nội dung:** viết lại `README.md` đạt chuẩn đánh giá giảng viên gồm 8 mục:
  thông tin dự án, sơ đồ kiến trúc + tree thư mục, bảng dịch vụ/cổng, hướng dẫn
  cài đặt 6 bước, báo cáo 3 mốc commit, hướng dẫn giám sát + 3 câu LogQL, bảng
  5 biện pháp Hardening, troubleshooting 9 lỗi thực tế (451 dòng, 44 khối lệnh).

---

# CHƯƠNG 4: HỆ THỐNG GIÁM SÁT HIỆU NĂNG VÀ QUẢN LÝ LOG TẬP TRUNG

## 4.1. Giám sát Prometheus & cAdvisor/Node Exporter

**Cấu hình scrape** (`prometheus/prometheus.yml`) — lấy mẫu mỗi **5 giây**:

```yaml
global:
  scrape_interval: 5s
  evaluation_interval: 5s
scrape_configs:
  - job_name: "prometheus"
    static_configs: [{ targets: ["localhost:9090"] }]
  - job_name: "cadvisor"
    static_configs: { targets: ["cadvisor:8080"] }
  - job_name: "node-exporter"
    static_configs: [{ targets: ["node-exporter:9100"] }]
```

**Kết quả Targets** (http://localhost:9090/targets): `prometheus → up`,
`cadvisor → up`, `node-exporter → up`, `Last Scrape: 5 seconds ago`.

**4 câu lệnh PromQL đo thực tế kèm giải thích:**

```promql
# (1) CPU (%) của container WordPress và Nginx — rate()/interval lấy đạo hàm
#     số giây CPU tích lũy, nhân 100 để ra phần trăm, gộp theo tên container
sum(rate(container_cpu_usage_seconds_total{name=~"dtc3_wordpress|dtc3_nginx"}[1m])) by (name) * 100

# (2) Bộ nhớ (byte) đang dùng của WordPress, Nginx, MySQL — đo tức thời
container_memory_usage_bytes{name=~"dtc3_wordpress|dtc3_nginx|dtc3_mysql"}

# (3) CPU (%) của MÁY HOST — lấy tỷ lệ phần trăm idle (node_cpu_seconds_total
#     mode="idle") rồi đảo ngược: 100% − idle% = utilization%
100 - (avg(rate(node_cpu_seconds_total{mode="idle"}[2m])) * 100)

# (4) Top 3 container tốn RAM nhất + băng thông nhận của Nginx (byte/giây)
topk(3, sum(container_memory_usage_bytes{name=~"dtc3.*"}) by (name))
sum(rate(container_network_receive_bytes_total{name="dtc3_nginx"}[2m]))
```

**Bảng 4.1.** Giá trị đo được từ môi trường triển khai thực tế:

| Truy vấn | Kết quả |
|---|---|
| CPU% `dtc3_mysql` / `dtc3_wordpress` / `dtc3_nginx` | `0.947` / `0.005` / `0` |
| RAM `dtc3_mysql` / `dtc3_wordpress` / `dtc3_nginx` | `362.1 MB` / `154.5 MB` / `13 MB` |
| CPU máy host | `3.43 %` |
| Top 3 RAM | mysql 362.1 MB, wordpress 154.5 MB, prometheus 94.1 MB |
| Nginx network RX | `1.2 B/s` |

## 4.2. Xây dựng Grafana Dashboard

1. Truy cập `http://localhost:3000`, đăng nhập `admin/admin` (bỏ qua đổi mật khẩu khi demo).
2. **Thêm Datasource Prometheus:** menu → *Connections → Data sources → Add data
   source → Prometheus* → URL `http://prometheus:9090` → **Save & test** →
   thông báo *"Data source is working"*.
3. **Import Dashboard mẫu:**
   - *Dashboards → Import → Import dashboard*;
   - nhập **Dashboard ID `14282`** (Docker Containers Monitoring) → **Load** →
     chọn datasource Prometheus vừa tạo → **Import**;
   - lặp lại với **ID `893`** (Docker Monitoring) nếu muốn thêm bộ dashboard.
4. Kết quả: các panel *CPU Usage, Memory Usage, Network I/O, Disk I/O* hiển thị
   đồ thị thời gian thực của từng container (filter theo nhãn
   `container_label_com_docker_compose_service` hoặc `name=~"dtc3.*"`).
5. **Thêm Datasource Loki** (*Connections → Data sources → Loki*, URL
   `http://loki:3100`) để dùng chung Grafana cho mục **Explore** truy vấn LogQL.

## 4.3. Quản lý Log tập trung Loki & Promtail

**Cơ chế thu thập:** Promtail dùng `docker_sd_configs` (qua `/var/run/docker.sock`)
để tự phát hiện mọi container đang chạy, đọc file log JSON tại
`/var/lib/docker/containers/<container-id>/<container-id>-json.log` (mount
read-only), parse bằng pipeline stage `docker`, gán nhãn qua `relabel_configs`:

```yaml
relabel_configs:
  - source_labels: ['__meta_docker_container_name']   # /dtc3_nginx -> dtc3_nginx
    regex: '/(.*)'
    target_label: 'container_name'
  - source_labels: ['container_name']                 # dtc3_nginx -> nginx
    regex: 'dtc3_(.*)'
    target_label: 'container_name'
  - source_labels: ['__meta_docker_container_label_com_docker_compose_service']
    target_label: 'compose_service'
  - source_labels: ['__meta_docker_container_log_path']
    target_label: '__path__'
pipeline_stages:
  - docker: {}
  - drop: { older_than: 1h, drop_counter_reason: "too_old" }
```

**3 câu lệnh LogQL thực tế** (thực thi tại Grafana Explore / API Loki):

```logql
# CÂU 1 — Toàn bộ log của Nginx Reverse Proxy
{container_name="nginx"}

# CÂU 2 — Lọc log chứa từ khóa lỗi của Nginx và WordPress
{container_name=~"nginx|wordpress"} |= "error"

# CÂU 3 — Thống kê tần suất log của Nginx trong 5 phút (log/giây)
rate({container_name="nginx"}[5m])
```

**Bảng 4.2.** Giải thích và kết quả thực tế của 3 câu LogQL:

| Câu | Loại truy vấn | Giải thích | Kết quả đo được |
|---|---|---|---|
| 1 | Stream selector | Chọn toàn bộ stream có nhãn `container_name` bằng đúng `nginx`, trả về dòng log gốc | 5+ dòng; VD `...[13/Oct/2026:04:08:41 +0000] "GET / HTTP/1.1" 302 0 ...` |
| 2 | Line filter (`\|=`) | Sau khi chọn stream regex `nginx\|wordpress`, giữ các dòng chứa chuỗi `error` | 3 dòng khớp (`/test-404-error-page`, `error_report.js`) |
| 3 | Metric query (`rate`) | Biến log thành đại lượng: số dòng log trên giây trong cửa sổ 5 phút trượt | 67 điểm dữ liệu, ~0.02–0.04 logs/sec |

> Ghép điều kiện lọc theo dự án khi máy có container trùng tên:
> `{container_name="mysql", compose_project="dtc245220014-detai3"} |= "error"`.

---

# CHƯƠNG 5: ĐÁNH GIÁ BẢO MẬT HARDENING HỆ THỐNG

## 5.1. Bang tong hop 5 giai phap Hardening da ap dung

**Bang 5.1.** Tong hop bien phap, vi tri cau hinh, lenh minh chung va ket qua.

| # | Bien phap | Cau hinh ap dung | Lenh kiem chung | Ket qua thuc te |
|---|---|---|---|---|
| 1 | Network Isolation | `docker-compose.yml`: `frontend_net` (bridge) + `backend_net` (bridge, `internal: true`); `db` chi thuoc `backend_net`, khong khai bao `ports`; `nginx` chi thuoc `frontend_net` | `docker inspect dtc3_mysql --format '{{.NetworkSettings.Networks}}'`; `docker port dtc3_mysql`; `docker network inspect ..._backend_net --format '{{.Internal}}'` | MySQL chi thay `backend_net`; `docker port` rong; `Internal=true` |
| 2 | Non-root / Restrict Privileges | Ca 10 service: `security_opt: ["no-new-privileges:true"]`; `prometheus` + `grafana` them `cap_drop: [ALL]` (chay san non-root: nobody 65534, grafana 472) | `docker inspect --format '{{.HostConfig.SecurityOpt}}'` (lap 10 container); `docker exec dtc3_prometheus id`; `docker exec dtc3_grafana id` | 10/10 container `no-new-privileges:true`; prometheus uid=65534, grafana uid=472 |
| 3 | Resource Limits | `db: 1g/1.0`; `wordpress, prometheus, cadvisor, grafana, loki: 512m`; `phpmyadmin, nginx, node-exporter, promtail: 256m/0.5` (xem Bang 5.2) | `docker inspect --format '{{.HostConfig.Memory}}'`; `docker stats --no-stream` | mysql 363.6MiB/1GiB (35.5%), nginx 13.14MiB/256MiB, prometheus 120MiB/512MiB |
| 4 | Security Headers & TLS Hardening | `nginx/default.conf`: `server_tokens off`; `ssl_protocols TLSv1.2 TLSv1.3`; cipher ECDHE manh; `ssl_prefer_server_ciphers on`; headers HSTS, X-Frame-Options, nosniff, XSS-Protection, CSP | `curl -skI https://localhost/`; `openssl s_client -connect` xem Protocol/Cipher; `s_client -tls1` phai fail; `nginx -t` | `Server: nginx` (an version); du 6 headers; TLS thuc te TLSv1.3 - TLS_AES_256_GCM_SHA384; TLS 1.0 bi tu choi |
| 5 | Secret Management | `.gitignore` chan `.env`, `*.crt/*.key`, `nginx/ssl/`; mat khau manh trong `.env` (khong commit) | `git check-ignore -v .env`; `git log --all --full-history -- .env`; `git ls-tree -r origin/main` | `.env` bi chan; lich su `.env` rong; remote khong co secret/key/cert |

**Bang 5.2.** Chi tiet Resource Limits tung service.

| Service | mem_limit | cpus | Ly do chon |
|---|---|---|---|
| db | 1g | 1.0 | MySQL InnoDB can RAM lon nhat |
| wordpress | 512m | 1.0 | PHP-Apache tai trung binh |
| phpmyadmin | 256m | 0.5 | Tai nhe, dung khi quan tri |
| nginx | 256m | 0.5 | Reverse proxy tinh, nhe |
| prometheus | 512m | 0.5 | TSDB ton RAM theo retention |
| cadvisor | 512m | 0.5 | Thu thap metrics lien tuc |
| node-exporter | 256m | 0.5 | Exporter nhe |
| grafana | 512m | 0.5 | Render dashboard |
| loki | 512m | 0.5 | Index log |
| promtail | 256m | 0.5 | Ship log nhe |

> Ghi chu: de bai goi y `user: "1000:1000"` nhung image chinh thuc can UID co
> dinh de doc file du lieu; ep UID 1000 gay loi permission. Nhom ap dung bien
> phap tuong duong manh hon: `no-new-privileges:true` cho 10 container +
> `cap_drop: [ALL]` va user non-root san co cua Prometheus/Grafana.

## 5.2. Danh gia tinh an toan thuc te cua he thong

1. **Giam be mat tan cong mang:** MySQL, Loki khong publish port; ke tan cong tu
   Internet chi thay 80/443 cua Nginx.
2. **Chong leo thang dac quyen:** `no-new-privileges` chan setuid leo root;
   `cap_drop: ALL` tuoc Linux capability.
3. **Chong can tai nguyen:** moi container co tran RAM/CPU; muc dung thuc te chi
   5-35% limit — vua chong DoS treo host, vua giu headroom cho burst hop le.
4. **Bao ve tang truyen tai va trinh duyet:** TLS 1.2+ cipher manh, HSTS ep HTTPS,
   X-Frame-Options chong clickjacking, nosniff chong MIME-sniffing, CSP han che
   XSS, an banner version chong fingerprinting.
5. **Khong lo bi mat:** password, private key chi ton tai local; lich su Git sach.
   Rui ro con lai: file `.env` plaintext tren host — khac phuc bang Docker
   Secrets/Vault (muc 6.2).
6. **Ket luan:** he thong dat muc an toan tot cho moi truong hoc tap va demo;
   du dieu kien nang cap staging sau khi bo sung cert CA hop le, backup ma hoa.

---

# CHƯƠNG 6: TROUBLESHOOTING & KẾT LUẬN

## 6.1. Bảng tổng hợp các lỗi phát sinh thực tế và cách xử lý

**Bảng 6.1.** 9 sự cố đã gặp trong quá trình triển khai thật.

| # | Hiện tượng / Lỗi | Nguyên nhân | Cách xử lý |
|---|---|---|---|
| 1 | `up -d` báo `bind: address already in use :80` | IIS (W3SVC/WAS, PID 4) chiếm cổng 80 trên Windows | PowerShell Admin: `Stop-Service W3SVC, WAS -Force`; kiểm tra `netstat -ano \| findstr ":80 "` trước khi `up` |
| 2 | `port is already allocated` (3000/9090/9100) | Container dự án khác đang giữ cổng | `docker ps --format "{{.Names}} {{.Ports}}"` xác định → `docker stop ...` (chỉ stop để khôi phục sau) |
| 3 | Nginx crash-loop `host not found in upstream "wordpress"` | Nginx khởi động trước khi DNS nội bộ sẵn sàng | `docker compose up -d --force-recreate nginx`; kiểm tra `nginx -t` |
| 4 | cAdvisor v0.47.0 lỗi `failed to identify the read-write layer ID`, 0 container | Bug upstream trên Docker 29 + containerd image store | Đổi image `ghcr.io/google/cadvisor:v0.60.5` (chứa bản vá) + ghi chú trong compose |
| 5 | Promtail lặp lỗi `400: entry too far behind`, log mới không vào Loki | Log cũ vượt giới hạn out-of-order 1h của Loki; positions mất khi recreate | `reject_old_samples: false`; `drop: older_than: 1h`; persist `./promtail/positions.yaml` (+ gitignore) |
| 6 | Trình duyệt cảnh báo cert tại `https://localhost` | Chứng chỉ tự ký không do CA tin cậy ký | Demo: Advanced → Proceed; sản phẩm thật: dùng Let's Encrypt (mục 6.2) |
| 7 | WordPress `Error establishing a database connection` / redirect-loop | DB chưa healthy; sai host/password trong `.env` | `depends_on` + healthcheck `mysqladmin ping`; đồng bộ password `.env` |
| 8 | `git push` bị rejected / chưa có upstream | Chưa pull/upstream | Lần đầu `git push -u origin main`; sau đó `git pull --rebase` rồi push |
| 9 | Grafana panel trống dù Prometheus UP | Sai URL datasource; filter label cũ `container=` thay vì `name=` | URL `http://prometheus:9090`; PromQL dùng `name=~"dtc3.*"` |

**Bộ lệnh chẩn đoán nhanh:**

```powershell
docker compose ps
docker compose logs --tail 50 <service>
docker compose exec nginx nginx -t
curl.exe -skI https://localhost/
curl.exe -s http://localhost:9090/api/v1/targets
curl.exe -s http://localhost:3100/ready
docker stats --no-stream
git status; git log --oneline -5
```

## 6.2. Kết luận và hướng phát triển nâng cấp hệ thống

**Kết luận.** Đồ án hoàn thành toàn bộ mục tiêu: website WordPress ổn định qua Nginx HTTPS; giám sát Prometheus + Grafana cho số liệu thật; log tập trung Loki + Promtail truy vấn bằng LogQL; 5 nhóm Hardening được chứng minh bằng lệnh kiểm tra; toàn bộ quá trình quản lý bằng 5 commit Git rõ ràng — hơn 1.000 dòng cấu hình, 10 service, 2 network, 5 volume, kiểm chứng trên môi trường thực tế.

**Hướng phát triển:**

1. Thay SSL tự ký bằng **Let's Encrypt + auto-renew**.
2. Chuyển secret sang **Docker Secrets / Vault**, backup MySQL mã hoá định kỳ.
3. Bổ sung **Alertmanager** (cảnh báo CPU/RAM/disk) và dashboard SLO.
4. Đa môi trường `docker-compose.override.yml` + CI/CD (lint → build → Trivy scan → deploy).
5. Quét lỗ hổng image định kỳ (**Trivy/Grype**), ký image (Cosign), bật audit log.

---

# PHỤ LỤC: DANH SÁCH HÌNH MINH CHỨNG CHÈN VÀO WORD

| Hình | Nội dung ảnh chụp | Vị trí chèn |
|---|---|---|
| H1 | `docker compose ps` — 10 container Up/healthy | Chương 3 |
| H2 | `https://localhost` → trang cài đặt WordPress | Chương 3 (Commit 1) |
| H3 | `https://localhost/phpmyadmin/` đăng nhập thành công | Chương 3 (Commit 1) |
| H4 | `curl -skI https://localhost` đủ Security Headers | Chương 5 |
| H5 | `http://localhost:9090/targets` — 3 target UP | Chương 4 |
| H6 | Prometheus Graph chạy PromQL có số liệu | Chương 4 |
| H7 | Grafana datasources Save & test xanh | Chương 4 |
| H8 | Dashboard ID 14282/893 có đồ thị thật | Chương 4 |
| H9 | Grafana Explore: 3 câu LogQL có kết quả | Chương 4 |
| H10 | `docker stats` + `docker inspect` limits | Chương 5 |
| H11 | Trang commits GitHub (5 commit đúng message) | Chương 3 |
| H12 | `git status` sạch sau push cuối | Chương 3 |

*— Hết nội dung báo cáo. Copy vào Word, áp dụng style Heading 1/2, chèn hình H1–H12, đánh số trang, xuất .docx để nộp. —*


