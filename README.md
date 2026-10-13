# ĐỀ 3 — WEBSITE QUẢNG BÁ SẢN PHẨM (WORDPRESS)

<div align="center">

![WordPress](https://img.shields.io/badge/WordPress-6.4-blue)
![Nginx](https://img.shields.io/badge/Nginx-1.25-alpine)
![MySQL](https://img.shields.io/badge/MySQL-8.0-orange)
![Docker](https://img.shields.io/badge/Docker-Compose-2478DC)
![Prometheus](https://img.shields.io/badge/Prometheus-v2.48.0-E6522C)
![Grafana](https://img.shields.io/badge/Grafana-10.2.2-F46800)
![Loki](https://img.shields.io/badge/Loki-2.9.2-2E9B8B)
![License](https://img.shields.io/badge/Student_Project-DTC245220014-green)

</div>

## 1. THÔNG TIN DỰ ÁN

| Hạng mục | Nội dung |
|---|---|
| **Họ và tên SV** | Nguyễn Minh Đức |
| **Lớp** | K23A — An toàn thông tin |
| **Môn học** | Triển khai và Quản trị Hệ thống Phần mềm (Mã học phần: DTC245220014) |
| **Tên đề tài** | Đề 3 — Website Quảng bá Sản phẩm (WordPress) |
| **Repository GitHub** | `DTC245220014-Detai3` |
| **Vai trò** | DevOps Engineer & System Administrator |
| **Công nghệ** | Docker Compose · Nginx Reverse Proxy + SSL · MySQL 8.0 · WordPress · phpMyAdmin · Prometheus · cAdvisor · Node-Exporter · Grafana · Loki · Promtail |

---

## 2. SƠ ĐỒ KIẾN TRÚC & CẤU TRÚC THƯ MỤC

### 2.1. Sơ đồ kiến trúc

```
                                ┌────────────────────────────────────────────────┐
                                │                   INTERNET / HOST              │
                                └───────┬──────────────┬───────────────┬─────────┘
                                   :80  │          :3000│          :9090│:9100 /:3100
                                        v              v              v
   ┌─────────────────────────── FRONTEND_NET (bridge) ─────────────────────────────┐
   │  ┌─────────────────────┐   ┌──────────────┐   ┌────────────┐   ┌───────────┐  │
   │  │ NGINX 1.25-alpine   │   │  GRAFANA     │   │ PROMETHEUS │   │   LOKI    │  │
   │  │ Reverse Proxy+SSL   │   │  :3000       │──>│  :9090     │   │  :3100    │  │
   │  │ :80 / :443          │   │  Dashboard   │   │  scrape 5s │   │  Log DB   │  │
   │  └──────┬──────────────┘   └──────▲───────┘   └──┬─────┬──┘   └─────▲─────┘  │
   │         │ proxy_pass              │    scrape    │     │           │ push    │
   │  ┌──────┴───────┐  ┌──────────────┴┐   ┌─────────┴─┐ ┌─┴────────┐ ┌─┴───────┐ │
   │  │ WORDPRESS    │  │ PHPMYADMIN    │   │ cAdvisor  │ │Node-Exp. │ │PROMTAIL │ │
   │  │ :80 (fpm)    │  │ :80           │   │ :8080     │ │ :9100    │ │ :9080   │ │
   │  └──────┬───────┘  └───────┬───────┘   │ container │ │  host    │ │ doc log │ │
   └─────────┼──────────────────┼───────────┤  metrics  │ │  metrics │ │  docker │
             │                  │           └───────────┘ └──────────┘ └────▲────┘
   ┌─────────┴── BACKEND_NET (bridge, internal: true) ──┴───────────────────┼──────┐
   │                    ┌──────────────────────┐      /var/lib/docker/containers   │
   │                    │  MySQL 8.0 :3306     │  <─── stdout json-log (RO)        │
   │                    │  KHONG expose port   │                                   │
   │                    └──────────────────────┘                                   │
   └───────────────────────────────────────────────────────────────────────────────┘
```

- **`frontend_net`** (bridge): Nginx, WordPress, phpMyAdmin, Prometheus, Grafana, Loki, cAdvisor, Node-Exporter, Promtail.
- **`backend_net`** (bridge, **`internal: true`**): MySQL + WordPress + phpMyAdmin — không có đường ra Internet, MySQL **không publish port** ra host (Network Isolation).

### 2.2. Cấu trúc thư mục dự án

```text
DTC245220014-Detai3/
├── .env                        # Biến môi trường, mật khẩu (KHÔNG commit)
├── .gitignore                  # Chặn .env, db_data/, nginx/ssl/, log, positions.yaml
├── README.md                   # Tài liệu dự án (file này)
├── docker-compose.yml          # 10 services, 2 networks, volumes, hardening
├── loki/
│   └── loki-config.yml         # Cấu hình Loki (standalone, filesystem storage)
├── nginx/
│   ├── default.conf            # Reverse proxy + SSL + Security Headers
│   └── ssl/                    # SSL self-signed (KHÔNG commit)
│       ├── nginx.crt
│       └── nginx.key
├── prometheus/
│   └── prometheus.yml          # Scrape interval 5s, 3 targets
└── promtail/
    ├── promtail-config.yml     # docker_sd + relabel container_name + drop stage
    └── positions.yaml          # Vị trí đọc log (runtime, KHÔNG commit)
```


---

## 3. BẢNG TỔNG HỢP DỊCH VỤ & CỔNG TRUY CẬP

| # | Service | Image | Container | Cổng trong container | Cổng publish ra host | URL truy cập | Vai trò |
|---|---|---|---|---|---|---|---|
| 1 | `db` | `mysql:8.0` | `dtc3_mysql` | 3306 | **không có** | — (chỉ nội bộ backend_net) | Cơ sở dữ liệu WordPress |
| 2 | `wordpress` | `wordpress:latest` | `dtc3_wordpress` | 80 | không có | qua Nginx → `https://localhost` | Website WordPress |
| 3 | `phpmyadmin` | `phpmyadmin:5.2` | `dtc3_phpmyadmin` | 80 | không có | `https://localhost/phpmyadmin/` | Giao diện quản trị MySQL |
| 4 | `nginx` | `nginx:1.25-alpine` | `dtc3_nginx` | 80, 443 | **80, 443** | `http://localhost` → redirect `https://localhost` | Reverse proxy + SSL + Security Headers |
| 5 | `prometheus` | `prom/prometheus:v2.48.0` | `dtc3_prometheus` | 9090 | **9090** | `http://localhost:9090` | Thu & lưu metrics (scrape 5s) |
| 6 | `cadvisor` | `ghcr.io/google/cadvisor:v0.60.5` | `dtc3_cadvisor` | 8080 | không có | qua Prometheus | Metrics từng container |
| 7 | `node-exporter` | `prom/node-exporter:v1.7.0` | `dtc3_nodeexporter` | 9100 | **9100** | `http://localhost:9100/metrics` | Metrics máy host |
| 8 | `grafana` | `grafana/grafana:10.2.2` | `dtc3_grafana` | 3000 | **3000** | `http://localhost:3000` | Dashboard giám sát & Explore (LogQL) |
| 9 | `loki` | `grafana/loki:2.9.2` | `dtc3_loki` | 3100 | **3100** | `http://localhost:3100` | Kho lưu log tập trung |
| 10 | `promtail` | `grafana/promtail:2.9.2` | `dtc3_promtail` | 9080 | không có | — | Thu log container → đẩy về Loki |

**Volumes:** `db_data` (MySQL), `nginx_logs`, `grafana_data`, `prometheus_data`, `loki_data`.

---

## 4. HƯỚNG DẪN CÀI ĐẶT & KHỞI CHẠY TỪNG BƯỚC

### Yêu cầu trước tiên

- Docker Desktop (Docker Engine + Docker Compose) đã cài và chạy.
- Git.
- Cổng trống: `80`, `443`, `3000`, `3100`, `9090`, `9100` (xem Mục 8 nếu bị chiếm).

### Bước 1 — Clone repository

```powershell
git clone https://github.com/dtc245220014-design/DTC245220014-Detai3.git
cd DTC245220014-Detai3
```

### Bước 2 — Kiểm tra file `.env` (mật khẩu mạnh, đã bị `.gitignore` chặn)

```powershell
Get-Content .env        # MYSQL_ROOT_PASSWORD, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE...
```

### Bước 3 — Sinh SSL Certificate tự ký (lưu vào `./nginx/ssl/`)

Cách A — máy có cài `openssl`:

```powershell
New-Item -ItemType Directory -Force -Path .\nginx\ssl
openssl req -x509 -nodes -days 365 -newkey rsa:2048 `
  -keyout .\nginx\ssl\nginx.key -out .\nginx\ssl\nginx.crt `
  -subj "/C=VN/ST=HaNoi/L=HaNoi/O=NguyenMinhDuc_K23A_ATTT/CN=localhost"
```

Cách B — không cài openssl, dùng Docker (cách đã dùng khi thực hiện dự án):

```powershell
New-Item -ItemType Directory -Force -Path .\nginx\ssl
docker run --rm -v "${PWD}/nginx/ssl:/ssl" alpine/openssl req -x509 -nodes -days 365 `
  -newkey rsa:2048 -keyout /ssl/nginx.key -out /ssl/nginx.crt `
  -subj "/C=VN/ST=HaNoi/L=HaNoi/O=NguyenMinhDuc_K23A_ATTT/CN=localhost"
```

### Bước 4 — Kiểm tra cấu hình & khởi chạy hệ thống

```powershell
docker compose config --quiet     # hợp lệ (không output = OK)
docker compose up -d              # khởi động toàn bộ 10 services
docker compose ps                 # 10/10 container Up (mysql/cadvisor: healthy)
```

Kiểm tra cú pháp Nginx bên trong container:

```powershell
docker compose exec nginx nginx -t
# -> nginx: configuration file /etc/nginx/nginx.conf test is successful
```

### Bước 5 — Kiểm tra truy cập

| Thành phần | URL | Kết quả kiểm chứng |
|---|---|---|
| Website WordPress | `https://localhost` | `302` → `/wp-admin/install.php` (bỏ qua cảnh báo Self-Signed) |
| phpMyAdmin | `https://localhost/phpmyadmin/` | `200` — đăng nhập bằng `MYSQL_USER`/`MYSQL_PASSWORD` trong `.env` |
| HTTP → HTTPS | `http://localhost` | `301` → `https://localhost/` |
| Prometheus | `http://localhost:9090` | Targets: `prometheus`, `cadvisor`, `node-exporter` = **UP** |
| Grafana | `http://localhost:3000` | `/api/health` → `{"database":"ok","version":"10.2.2"}` (admin/admin) |
| Loki | `http://localhost:3100/ready` | `ready` (HTTP 200) |
| Node-Exporter | `http://localhost:9100/metrics` | `200` |

### Bước 6 — Dừng / dọn dẹp

```powershell
docker compose down          # dừng nhưng GIỮ dữ liệu (DB, Grafana, Loki...)
docker compose down -v       # dừng và XÓA toàn bộ volume (reset 100%)
docker compose logs -f       # xem log toàn hệ thống
```

---

## 5. BÁO CÁO 3 MỐC COMMIT GIT

### 5.1. Bảng tổng hợp các mốc commit

| Mốc | Hash | Message (chính thức) | Nội dung thay đổi | Files |
|---|---|---|---|---|
| **Commit 1** | `9c44a0e` | `Commit 1: Deploy wordpress app with Nginx reverse proxy and HTTPS` | Core website: WordPress + MySQL + phpMyAdmin + Nginx Reverse Proxy SSL | `.gitignore`, `docker-compose.yml`, `nginx/default.conf`, `README.md` |
| **Commit 2** | `70cecad` | `Commit 2: Add Prometheus metrics and Grafana monitoring` | Hệ thống giám sát: Prometheus + cAdvisor + Node-Exporter + Grafana | `docker-compose.yml`, `prometheus/prometheus.yml`, `README.md` |
| **Commit 3** | `995752e` | `Commit 3: Add centralized logging with Loki and Promtail` | Log tập trung: Loki + Promtail + cấu hình LogQL | `docker-compose.yml`, `loki/loki-config.yml`, `promtail/promtail-config.yml`, `README.md` |
| Commit 4 *(nâng cao)* | `0db29ca` | `Commit 4: Apply security hardening with resource limits and TLS hardening` | Hardening: NNP, cap_drop, resource limits, cipher mạnh | `.gitignore`, `docker-compose.yml`, `nginx/default.conf`, `promtail/promtail-config.yml`, `README.md` |

### 5.2. Chi tiết Commit 1 — Triển khai website WordPress + Nginx Reverse Proxy + HTTPS

- **Ý nghĩa:** Đưa toàn bộ mã nguồn và cấu hình cốt lõi của hệ thống vào Git: file
  `.gitignore` (chặn secret), `.env` (mật khẩu MySQL/WordPress mạnh — không commit),
  `docker-compose.yml` với 4 service đầu tiên (`db`, `wordpress`, `phpmyadmin`, `nginx`)
  và 2 network isolation `frontend_net`/`backend_net`, cấu hình Nginx reverse proxy
  (`proxy_pass http://wordpress:80`), SSL self-signed, Security Headers, `server_tokens off`.
- **Lệnh thực hiện:**

```powershell
git add .
git commit -m "Commit 1: Deploy wordpress app with Nginx reverse proxy and HTTPS"
git push -u origin main
```

- **Kết quả:** `4 files changed, 313 insertions(+)` — push thành công `origin/main`,
  kiểm chứng: `https://localhost` → 302 install.php, `https://localhost/phpmyadmin/` → 200.

### 5.3. Chi tiết Commit 2 — Tích hợp Prometheus + Grafana

- **Ý nghĩa:** Mở rộng `docker-compose.yml` thêm 4 service giám sát
  (`prometheus` v2.48.0 port 9090, `cadvisor`, `node-exporter` v1.7.0 port 9100,
  `grafana` 10.2.2 port 3000 + volume `grafana_data`) và file `prometheus/prometheus.yml`
  với `scrape_interval: 5s`, 3 scrape_configs (`prometheus`, `cadvisor`, `node-exporter`).
- **Lệnh thực hiện:**

```powershell
docker compose up -d
git add .
git commit -m "Commit 2: Add Prometheus metrics and Grafana monitoring"
git push
```

- **Kết quả:** `3 files changed, 199 insertions(+), 5 deletions(-)` — push
  `9c44a0e..70cecad`; Prometheus Targets 3/3 **UP**, số liệu thật:
  `MEM dtc3_mysql = 362.1 MB`, `HOST_CPU_USAGE% = 3.43`.
- **Ghi chú kỹ thuật:** dùng `ghcr.io/google/cadvisor:v0.60.5` thay `v0.47.0` theo đề
  (Docker 29 dùng containerd-snapshotter, bản cũ không đọc được — google/cadvisor#3643).

### 5.4. Chi tiết Commit 3 — Tích hợp Loki + Promtail (log tập trung)

- **Ý nghĩa:** Thêm 2 service `loki` (v2.9.2, port 3100, filesystem storage) và
  `promtail` (v2.9.2, mount `/var/lib/docker/containers` + `docker.sock`), cấu hình
  tự phát hiện container (`docker_sd_configs`), tự gán nhãn `container_name`
  (bỏ prefix `dtc3_` → khớp query `{container_name="nginx"}`), pipeline `docker` +
  `drop older_than:1h`, positions được persist.
- **Lệnh thực hiện:**

```powershell
docker compose up -d
git add .
git commit -m "Commit 3: Add centralized logging with Loki and Promtail"
git push
```

- **Kết quả:** `4 files changed, 236 insertions(+), 2 deletions(-)` — push
  `70cecad..995752e`; 3 câu LogQL chạy thật (xem Mục 6.3).

---

## 6. HƯỚNG DẪN GIÁM SÁT PROMETHEUS/GRAFANA & LOGQL

### 6.1. Giám sát Metrics (Prometheus + Grafana)

**Kiểm tra Prometheus** — mở `http://localhost:9090`:

- **Status → Targets**: 3 target `prometheus`, `cadvisor`, `node-exporter` đều **UP**, `Last Scrape: 5 seconds ago`.
- **Graph**: nhập PromQL mẫu:

```promql
# CPU (%) của container wordpress, nginx, mysql
sum(rate(container_cpu_usage_seconds_total{name=~"dtc3_wordpress|dtc3_nginx|dtc3_mysql"}[1m])) by (name) * 100

# Bộ nhớ (bytes) của các container trong dự án
container_memory_usage_bytes{name=~"dtc3_wordpress|dtc3_nginx|dtc3_mysql"}

# CPU (%) của MÁY HOST (node-exporter)
100 - (avg(rate(node_cpu_seconds_total{mode="idle"}[2m])) * 100)

# Top 3 container tốn RAM nhất
topk(3, sum(container_memory_usage_bytes{name=~"dtc3.*"}) by (name))
```

Kết quả đo thực tế: `CPU% dtc3_mysql = 0.947`, `MEM dtc3_mysql = 362.1 MB`,
`MEM dtc3_wordpress = 154.5 MB`, `HOST_CPU_USAGE% = 3.43`.

**Cấu hình Grafana** — mở `http://localhost:3000` (admin/admin):

1. **Connections → Data sources → Add data source → Prometheus** → URL:
   `http://prometheus:9090` → **Save & test** → *"Data source is working"*.
2. **Dashboards → Import** → nhập ID **`14282`** (Docker Containers Monitoring) →
   **Load** → chọn datasource → **Import** (hoặc ID **`893`** — Docker Monitoring).
3. Mở dashboard: panel CPU / Memory / Network / I/O của container hiển thị số liệu thời gian thực.

### 6.2. Cấu hình Datasource Loki (cho LogQL)

1. **Connections → Data sources → Add data source → Loki** → URL: `http://loki:3100`
   → **Save & test**.
2. Mở **Explore** (biểu tượng kính lúp) → chọn datasource **Loki** → nhập LogQL → **Run query**.

### 6.3. 3 câu lệnh LogQL mẫu (thực thi trên Grafana Explore)

```logql
# CÂU 1 — Toàn bộ log của Nginx Reverse Proxy
{container_name="nginx"}

# CÂU 2 — Lọc log chứa từ khóa lỗi của Nginx và WordPress
{container_name=~"nginx|wordpress"} |= "error"

# CÂU 3 — Thống kê tần suất log của Nginx trong 5 phút (đơn vị: log/giây)
rate({container_name="nginx"}[5m])
```

**Giải thích & kết quả thực tế:**

| Câu | Loại truy vấn | Giải thích | Kết quả đo được |
|---|---|---|---|
| 1 | *Stream selector* | `{container_name="nginx"}` chọn toàn bộ stream log có nhãn bằng đúng `nginx`, trả về từng dòng log gốc | VD: `172.30.0.1 - - [13/Oct/2026:04:08:41 +0000] "GET / HTTP/1.1" 302 0 "-" "curl/8.21.0"` |
| 2 | *Line filter* | Sau khi chọn stream regex `nginx\|wordpress`, giữ dòng chứa chuỗi `error` (`\|=`) | 3 dòng khớp (`/test-404-error-page`, `error_report.js`) |
| 3 | *Metric query* | `rate(...[5m]` chuyển log thành số: số log trên giây trong cửa sổ 5 phút trượt | 67 điểm dữ liệu, ~0.02–0.04 logs/sec |

> Label `container_name` có sẵn: `nginx`, `wordpress`, `mysql`, `phpmyadmin`,
> `prometheus`, `grafana`, `loki`... (Promtail đã relabel bỏ prefix `dtc3_`).
> Phân biệt container trùng tên máy khác: `{container_name="mysql", compose_project="dtc245220014-detai3"}`.

---

## 7. TỔNG HỢP CÁC BIỆN PHÁP HARDENING ĐÃ ÁP DỤNG

| # | Biện pháp | Cấu hình đã áp dụng | Lệnh minh chứng | Kết quả thực tế |
|---|---|---|---|---|
| **1** | **Network Isolation** | 2 network: `frontend_net` + `backend_net` (`internal: true`); `db` chỉ thuộc `backend_net`, không khai báo `ports` | `docker inspect dtc3_mysql -f '{{range $k,$v := .NetworkSettings.Networks}}{{$k}} {{end}}'`<br>`docker port dtc3_mysql`<br>`docker network inspect dtc245220014-detai3_backend_net -f 'Internal={{.Internal}}'` | MySQL → chỉ `backend_net`; `docker port` **rỗng**; `Internal=true` (không ra Internet); Nginx → chỉ `frontend_net` |
| **2** | **Non-root / Restrict Privileges** | Cả 10 service: `security_opt: ["no-new-privileges:true"]`; Prometheus + Grafana thêm `cap_drop: ["ALL"]` | `docker inspect dtc3_nginx -f '{{json .HostConfig.SecurityOpt}}'`<br>`docker inspect dtc3_prometheus -f '{{json .HostConfig.CapDrop}}'`<br>`docker exec dtc3_prometheus id` | 10/10 container `["no-new-privileges:true"]`; Prometheus `uid=65534(nobody)`, Grafana `uid=472(grafana)` |
| **3** | **Resource Limits** | `mem_limit` + `cpus`: MySQL `1g/1.0`, WordPress `512m/1.0`, monitoring `512m/0.5`, còn lại `256m/0.5` | `docker inspect dtc3_mysql -f 'MemLimit={{.HostConfig.Memory}} CPU={{.HostConfig.NanoCpus}}'`<br>`docker stats --no-stream` | `MemLimit=1073741824 CPU=1000000000`; stats: `dtc3_mysql 363.6MiB/1GiB`, `dtc3_nginx 13MiB/256MiB` — mọi container dưới limit |
| **4** | **Security Headers & TLS Hardening** | `server_tokens off`; TLS 1.2/1.3; cipher `ECDHE-…GCM/CHACHA20:!aNULL:!MD5:!RC4:!3DES`; `ssl_session_tickets off`; HSTS, X-Frame-Options, X-Content-Type-Options, X-XSS-Protection, CSP, Referrer-Policy | `curl -skI https://localhost/`<br>`docker run --rm -i alpine/openssl s_client -connect host.docker.internal:443`<br>`... s_client … -tls1`<br>`docker compose exec nginx nginx -t` | `Server: nginx` (ẩn version) + đủ headers; thực tế `TLSv1.3 / TLS_AES_256_GCM_SHA384`; TLS 1.0 **handshake fail**; `nginx -t` OK |
| **5** | **Quản lý bí mật** | `.gitignore` chặn `.env`, `nginx/ssl/` (`.crt/.key`), `promtail/positions.yaml`, `db_data/`, log | `git check-ignore -v .env`<br>`git ls-files \| Select-String '\.env$'`<br>`git log --all --full-history -- .env`<br>`git ls-tree -r origin/main --name-only` | Ignore theo `.gitignore:6`; không tracked; lịch sử rỗng; trên GitHub **không có** `.env`/`.key`/`.crt` |

> Lưu ý về `user: "1000:1000"`: Nginx/MySQL/WordPress cần root ở entrypoint để bind
> port <1024 rồi tự hạ quyền (gosu/setuid) — biện pháp thay thế đã áp dụng là
> **`no-new-privileges: true`** (chặn mọi cách nâng quyền khi exec) kết hợp
> **`cap_drop: ALL`** cho các service đã chạy non-root.

---

## 8. HƯỚNG DẪN XỬ LÝ LỖI THƯỜNG GẶP (TROUBLESHOOTING)

### 8.1. `bind: An attempt was made to access a socket...` khi `docker compose up -d` (cổng 80)

**Nguyên nhân:** IIS (W3SVC) đang giữ cổng 80, hoặc Windows đưa 80 vào danh sách
*excluded port range*.

```powershell
netstat -ano | findstr ":80 "            # tìm PID chiếm cổng
netsh interface ipv4 show excludedportrange protocol=tcp
# Chạy PowerShell QUYỀN ADMIN:
Stop-Service W3SVC, WAS -Force            # dừng IIS (phục hồi: Start-Service WAS, W3SVC)
```

### 8.2. `port is already allocated` (container Docker khác chiếm 80/443/3000/9100)

```powershell
docker ps --format "table {{.Names}}\t{{.Ports}}"        # container nào giữ port?
docker stop nginx-proxy-manager grafana node-exporter    # tạm dừng container xung đột
# Phục hồi sau khi chụp ảnh xong:
docker start nginx-proxy-manager grafana node-exporter
```

### 8.3. Nginx restart loop: `host not found in upstream "wordpress"`

**Nguyên nhân:** container nginx chưa gắn vào `frontend_net` (sau sự cố bound port
lúc `up` hoặc network bị tạo lại) → không phân giải được tên service.

```powershell
docker compose up -d --force-recreate nginx
docker compose exec nginx nginx -t
```

### 8.4. cAdvisor không hiện container / `failed to identify the read-write layer ID`

**Nguyên nhân:** Docker Desktop 29 dùng containerd-snapshotter; cAdvisor ≤ v0.53
không đọc được layout mới (google/cadvisor#3643 — fix phát hành từ v0.54.0).
**Giải pháp đã áp dụng trong dự án:** dùng image
`ghcr.io/google/cadvisor:v0.60.5` (xem chú thích trong `docker-compose.yml`).

### 8.5. Promtail lỗi `400 ... timestamp too old` / `entry too far behind`

**Nguyên nhân:** file log chứa entry cũ (container khác) — Loki chỉ nhận entry
trong **1 giờ** gần nhất (`max_chunk_age/2`) cho stream đã có dữ liệu; batch bị
reject lặp vô hạn và chặn log mới.

```powershell
docker compose logs --since 60s promtail | Select-String "error sending batch"
# Cấu hình phòng ngừa đã có trong promtail-config.yml:
#   pipeline_stages:  - drop: { older_than: 1h }          # bỏ entry cũ > 1h
#   volumes: ./promtail/positions.yaml:/tmp/positions.yaml # giữ vị trí đọc (không replay)
docker compose up -d promtail
```

### 8.6. Trình duyệt cảnh báo "Not private / Self-Signed Certificate"

Certificate tự ký là bình thường với HTTPS demo → chọn **Advanced → Proceed to
localhost (unsafe)**. Kiểm tra thông tin TLS thật:

```powershell
docker run --rm -i alpine/openssl s_client -connect host.docker.internal:443 -servername localhost
# -> Protocol: TLSv1.3, Cipher: TLS_AES_256_GCM_SHA384, Verify: self-signed certificate
```

### 8.7. WordPress không mở / vòng lặp / MySQL chưa sẵn sàng

```powershell
docker compose ps                          # db phải "healthy"
docker compose logs db --tail 50
docker compose up -d --force-recreate wordpress
# Reset hoàn toàn nếu dữ liệu DB hỏng:
docker compose down -v
docker compose up -d
```

### 8.8. `git push` thất bại (quyền / credential / remote có commit mới)

```powershell
git remote -v                              # đúng repo DTC245220014-Detai3
git status                                 # working tree phải sạch
git pull --rebase origin main              # đồng bộ nếu remote có commit mới
git push -u origin main                    # đăng nhập GitHub khi được yêu cầu
```

### 8.9. Bộ lệnh chẩn đoán nhanh toàn hệ thống

```powershell
docker compose ps                          # 10/10 Up (mysql/cadvisor: healthy)
docker compose logs --since 5m promtail    # không có "error sending batch"
curl -skI https://localhost                # đủ Security Headers
curl http://localhost:9090/api/v1/targets  # 3 targets "health":"up"
curl http://localhost:3100/ready           # "ready"
```

---

<div align="center">

**Nguyễn Minh Đức — K23A An toàn thông tin**
**Đề 3: Website Quảng bá Sản phẩm (WordPress) — Repository `DTC245220014-Detai3`**

![Commits](https://img.shields.io/badge/Commits-4-blue)
![Services](https://img.shields.io/badge/Services-10-green)
![Hardening](https://img.shields.io/badge/Hardening-5%20measures-orange)

</div>





