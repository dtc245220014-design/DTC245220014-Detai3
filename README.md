# DTC245220014-Detai3 — Đề 3: Website Quảng bá Sản phẩm (WordPress)

Sinh viên: Nguyễn Minh Đức — Lớp K23A (An toàn thông tin)

## Kiến trúc (Commit 1 + Commit 2)

```
Internet/User
     |  :80 / :443
 [Nginx Reverse Proxy + SSL]  (frontend_net)
     |               \
     v                v
 [WordPress]      [phpMyAdmin]   (frontend_net + backend_net)
     |                 |
     +--------+--------+
              v
        [MySQL 8.0]    (backend_net - internal)

 [Prometheus :9090] <--scrape 5s-- cAdvisor :8080 (container metrics)
         ^                        Node-Exporter :9100 (host metrics)
         |                                ^
 [Grafana :3000] -------------------------+
         |  (Explore - LogQL)
 [Loki :3100] <----push---- Promtail (doc /var/lib/docker/containers)
```

## Chuẩn bị SSL tự ký (PowerShell)

```powershell
New-Item -ItemType Directory -Force -Path .\nginx\ssl
openssl req -x509 -nodes -days 365 -newkey rsa:2048 `
  -keyout .\nginx\ssl\nginx.key -out .\nginx\ssl\nginx.crt `
  -subj "/C=VN/ST=HaNoi/L=HaNoi/O=NguyenMinhDuc_K23A_ATTT/CN=localhost"
```

## Kiểm tra cấu hình Nginx

```powershell
docker compose run --rm --no-deps nginx nginx -t
```

## Khởi chạy hệ thống

```powershell
docker compose up -d
docker compose ps
docker compose logs -f
```

## Kiểm tra truy cập

- Website WordPress: https://localhost  (bỏ qua cảnh báo Self-Signed Certificate)
- phpMyAdmin: https://localhost/phpmyadmin/ (đăng nhập bằng MYSQL_USER / MYSQL_PASSWORD trong `.env`)

## Dừng / dọn dẹp

```powershell
docker compose down          # dừng nhưng giữ dữ liệu DB
docker compose down -v       # dừng và xóa toàn bộ dữ liệu DB
```

## HỆ THỐNG GIÁM SÁT (Commit 2)

### Các service giám sát

| Service | Image | Cổng | Vai trò |
|---|---|---|---|
| prometheus | prom/prometheus:v2.48.0 | 9090 | Thu & lưu metrics, scrape mỗi 5s |
| cadvisor | ghcr.io/google/cadvisor:v0.60.5 | (nội bộ 8080) | Metrics từng container (CPU/RAM/Network/IO) |
| node-exporter | prom/node-exporter:v1.7.0 | 9100 | Metrics máy host (CPU/RAM/đĩa/mạng) |
| grafana | grafana/grafana:10.2.2 | 3000 | Dashboard trực quan, volume `grafana_data` |

> **Lưu ý version cAdvisor:** đề bài nêu `gcr.io/cadvisor/cadvisor:v0.47.0`,
> nhưng Docker Desktop 29 dùng containerd-snapshotter nên bản v0.47.0 báo lỗi
> `failed to identify the read-write layer ID` và **không thấy container nào**
> (xem google/cadvisor#3643, fix ở PR #3709 phát hành từ v0.54.0). Do đó dùng
> image chính thức mới `ghcr.io/google/cadvisor:v0.60.5` để dashboard có số liệu thật.

### Kiểm tra Prometheus

- Mở http://localhost:9090 → mục **Status → Targets**: 3 target `prometheus`,
  `cadvisor`, `node-exporter` đều trạng thái **UP**.
- Mở http://localhost:9090/graph nhập PromQL (xem danh sách mẫu bên dưới).

### Hướng dẫn Grafana (http://localhost:3000)

1. Đăng nhập: user `admin`, mật khẩu `admin` → đổi mật khẩu khi được yêu cầu (có thể Skip).
2. **Thêm Datasource Prometheus:**
   - Menu (góc trái) → **Connections → Data sources → Add data source → Prometheus**.
   - URL: `http://prometheus:9090`
   - Scroll xuống bấm **Save & test** → hiệngreen *"Data source is working"*.
3. **Import Dashboard mẫu giám sát Docker:**
   - Menu → **Dashboards → Import → Import dashboard**.
   - Nhập Dashboard ID `14282` (Docker Containers Monitoring) → bấm **Load** →
     chọn datasource Prometheus vừa tạo → **Import**.
   - Lặp lại với ID `893` (Docker Monitoring) nếu muốn thêm.
4. Mở dashboard vừa import → các panel CPU, Memory, Network, I/O của container
   WordPress/Nginx/MySQL hiển thị số liệu thời gian thực.

### 4 câu lệnh PromQL mẫu (kiểm tra trên http://localhost:9090/graph)

```promql
# 1. CPU usage (%) của container wordpress và nginx
sum(rate(container_cpu_usage_seconds_total{name=~"dtc3_wordpress|dtc3_nginx"}[1m])) by (name) * 100

# 2. Memory usage (bytes) của wordpress, nginx, mysql
container_memory_usage_bytes{name=~"dtc3_wordpress|dtc3_nginx|dtc3_mysql"}

# 3. CPU usage của MÁY HOST (node-exporter)
100 - (avg(rate(node_cpu_seconds_total{mode="idle"}[2m])) * 100)

# 4. 3 container tốn RAM nhất + băng thông nhận của nginx
topk(3, sum(container_memory_usage_bytes{name=~"dtc3.*"}) by (name))
sum(rate(container_network_receive_bytes_total{name="dtc3_nginx"}[2m]))
```

Kết quả đo thực tế trên máy (ví dụ):
`CPU% dtc3_mysql = 0.947`, `MEM dtc3_mysql = 362.1 MB`,
`MEM dtc3_wordpress = 154.5 MB`, `MEM dtc3_nginx = 13 MB`,
`HOST_CPU_USAGE% = 3.43`.

## HỆ THỐNG LOG TẬP TRUNG — LOKI + PROMTAIL (Commit 3)

### Các service log

| Service | Image | Cổng | Vai trò |
|---|---|---|---|
| loki | grafana/loki:2.9.2 | 3100 | Nhận & lưu log (filesystem, volume `loki_data`) |
| promtail | grafana/promtail:2.9.2 | (9080 nội bộ) | Đọc log stdout của mọi container, push về Loki |

- `loki/loki-config.yml`: cấu hình standalone (filesystem storage, boltdb-shipper index,
  `reject_old_samples: false` để log cũ của container khác không làm hỏng batch push).
- `promtail/promtail-config.yml`:
  - `docker_sd_configs` qua `/var/run/docker.sock` → tự phát hiện container đang chạy.
  - Đọc file log `/var/lib/docker/containers/<id>/<id>-json.log` (mount read-only).
  - Relabel tự gán nhãn **`container_name`** (bỏ dấu `/` và bỏ prefix `dtc3_`
    để `dtc3_nginx` → `nginx`), kèm `container_full_name`, `compose_service`, `compose_project`.
  - Pipeline `docker` parse JSON + `drop: older_than: 168h` (loại log quá 7 ngày,
    tránh lỗi out-of-order `entry too far behind` của Loki — giới hạn `max_chunk_age/2` = 1h).

### Thêm Loki Datasource trong Grafana

1. http://localhost:3000 → **Connections → Data sources → Add data source → Loki**.
2. URL: `http://loki:3100` → **Save & test** → *"Data source is working"*.
3. Vào **Explore** (góc trái, biểu tượng kính lúp) → chọn datasource Loki → nhập LogQL.

### 3 câu LogQL demo (thực thi trên Grafana Explore / http://localhost:3100)

```logql
# CÂU 1 - Toàn bộ log của Nginx Reverse Proxy
{container_name="nginx"}

# CÂU 2 - Lọc log chứa từ khóa lỗi của Nginx và WordPress
{container_name=~"nginx|wordpress"} |= "error"

# CÂU 3 - Thống kê tần suất log của Nginx trong 5 phút (log/giây)
rate({container_name="nginx"}[5m])
```

**Giải thích:**
- **Câu 1** — *Log stream selector*: `{container_name="nginx"}` chọn toàn bộ stream log có
  nhãn đúng bằng `nginx`, trả về từng dòng log gốc (đối chiếu với label
  `GET / HTTP/1.1" 302`, `GET /phpmyadmin/ ... 200` của nginx access log).
- **Câu 2** — `|= "error"` là *line filter*: giữ lại các dòng sau khi chọn stream
  có regex `nginx|wordpress` và dòng chứa chuỗi `error` (không phân biệt hoa thường
  theo mặc định là phân biệt — chuỗi phải khớp `error`). Kết quả thực tế: 3 dòng
  (URL `/test-404-error-page`, `error_report.js` của phpMyAdmin).
- **Câu 3** — `rate({...}[5m])` là *metric query* biến log thành số: mỗi giây Loki tính
  số log trong cửa sổ 5 phút trượt → đơn vị **logs/second**; đo được 67 điểm dữ liệu,
  giá trị 0.02–0.04 logs/s (phù hợp traffic curl demo).

**Kết quả đo thực tế qua API Loki:**
```
Câu 1: {container_name="nginx"}            -> 5+ dòng, vd:
  172.30.0.1 - - [13/Oct/2026:03:43:33 +0000] "GET / HTTP/1.1" 302 0 "-" "curl/8.21.0"
Câu 2: |= "error"                          -> 3 dòng khớp
Câu 3: rate([5m])                          -> 67 điểm, ~0.02-0.04 logs/sec
Label container_name có sẵn: nginx, wordpress, mysql, phpmyadmin, prometheus, ...
```

> Lưu ý: nếu một container khác của máy trùng tên (vd container `mysql` cũ),
  phân biệt bằng thêm nhãn: `{container_name="mysql", compose_project="dtc245220014-detai3"}`.

## HARDENING BẢO MẬT (Giai đoạn 4) — 5 biện pháp

### #1 Network Isolation (tách mạng + không expose DB)

- `docker-compose.yml` khai báo 2 network: `frontend_net` (bridge) và `backend_net`
  (**`internal: true`** — container trong mạng này KHÔNG có đường ra Internet).
- `db` (MySQL) chỉ thuộc `backend_net`, **không khai báo `ports`** → không port nào
  của MySQL được publish ra host. WordPress/ phpMyAdmin là cầu nối qua 2 mạng;
  Nginx chỉ nằm `frontend_net`.

```powershell
# Minh chứng:
docker inspect dtc3_mysql -f '{{range $k,$v := .NetworkSettings.Networks}}{{$k}} {{end}}'
#  -> dtc245220014-detai3_backend_net
docker port dtc3_mysql                 # -> (rỗng, không có port ra host)
docker network inspect dtc245220014-detai3_backend_net -f 'Internal={{.Internal}}'
#  -> Internal=true
docker inspect dtc3_nginx -f '{{range $k,$v := .NetworkSettings.Networks}}{{$k}} {{end}}'
#  -> dtc245220014-detai3_frontend_net
```

### #2 Non-root Container / Restrict Privileges

- **Toàn bộ 10 service** khai báo `security_opt: ["no-new-privileges:true"]`
  (chặn mọi cơ chế nâng quyền: setuid, file capabilities khi exec).
- **Prometheus (chạy `uid=65534 nobody`) và Grafana (chạy `uid=472 grafana`)** thêm
  `cap_drop: ["ALL"]` — bỏ toàn bộ Linux capability vì đã chạy non-root.
- Các service không dùng `user: "1000:1000"` vì Nginx/MySQL/WordPress cần root ở
  entrypoint để bind port <1024 rồi tự hạ quyền (gosu/setuid) — thay bằng NNP là đủ an toàn.

```powershell
# Minh chứng:
docker inspect dtc3_nginx -f '{{json .HostConfig.SecurityOpt}}'
#  -> ["no-new-privileges:true"]   (làm tương tự cho 10 container)
docker inspect dtc3_prometheus -f '{{json .HostConfig.CapDrop}}'   # -> ["ALL"]
docker exec dtc3_prometheus id     # -> uid=65534(nobody)
docker exec dtc3_grafana id        # -> uid=472(grafana)
```

### #3 Resource Limits (giới hạn CPU/RAM từng container)

| Container | mem_limit | cpus |
|---|---|---|
| db (MySQL) | 1g | 1.0 |
| wordpress | 512m | 1.0 |
| phpmyadmin / nginx / node-exporter / promtail | 256m | 0.5 |
| prometheus / cadvisor / grafana / loki | 512m | 0.5 |

```powershell
# Minh chứng:
docker inspect dtc3_mysql -f 'MemLimit={{.HostConfig.Memory}} CPU={{.HostConfig.NanoCpus}}'
#  -> MemLimit=1073741824 (1GB) CPU=1000000000 (1 CPU)
docker stats --no-stream   # bảng thực tế: dtc3_mysql 363.6MiB/1GiB, dtc3_nginx 13MiB/256MiB...
```

### #4 Security Headers & TLS Hardening (Nginx)

- `nginx/default.conf`: `server_tokens off;`, redirect 80→443.
- TLS: `ssl_protocols TLSv1.2 TLSv1.3;` (tắt SSLv3/TLS1.0/1.1),
  whitelist cipher mạnh `ECDHE-...GCM/CHACHA20:!aNULL:!MD5:!RC4:!3DES`,
  `ssl_prefer_server_ciphers on; ssl_session_tickets off;`.
- Headers: `X-Frame-Options`, `X-Content-Type-Options`, `X-XSS-Protection`,
  `Content-Security-Policy`, `Referrer-Policy`, `Strict-Transport-Security (HSTS)`.

```powershell
# Minh chứng headers:
curl -skI https://localhost/
#  -> Server: nginx (khong co version), X-Frame-Options, CSP, HSTS...
# Minh chứng phien TLS (tu container openssl):
docker run --rm -i alpine/openssl s_client -connect host.docker.internal:443 -servername localhost
#  -> Protocol: TLSv1.3, Cipher is TLS_AES_256_GCM_SHA384
docker run --rm -i alpine/openssl s_client -connect host.docker.internal:443 -tls1
#  -> handshake that bai (TLS 1.0 bi tu choi)
docker compose exec nginx nginx -t    # -> syntax is ok / test is successful
```

### #5 Quản lý bí mật (không commit `.env`)

- `.gitignore` chặn `.env`, `nginx/ssl/` (cert/key), `promtail/positions.yaml`, log, db_data.

```powershell
# Minh chứng:
git check-ignore -v .env              # -> .gitignore:6:.env  .env
git ls-files | Select-String '\.env$' # -> (rỗng - khong tracked)
git log --all --full-history -- .env  # -> (rong - khong tung commit)
git ls-tree -r origin/main --name-only
#  -> chi co .gitignore, README.md, docker-compose.yml, loki/, nginx/default.conf,
#     prometheus/, promtail/  (KHONG co .env, .key, .crt)
```

## Git — Commit 1

```powershell
git add .
git commit -m "Commit 1: Deploy wordpress app with Nginx reverse proxy and HTTPS"
git push -u origin main
```

## Git — Commit 2

```powershell
docker compose up -d
git add .
git commit -m "Commit 2: Add Prometheus metrics and Grafana monitoring"
git push
```

## Git — Commit 3

```powershell
docker compose up -d
git add .
git commit -m "Commit 3: Add centralized logging with Loki and Promtail"
git push
```
