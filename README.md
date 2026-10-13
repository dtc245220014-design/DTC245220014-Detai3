# DTC245220014-Detai3 — Đề 3: Website Quảng bá Sản phẩm (WordPress)

Sinh viên: Nguyễn Minh Đức — Lớp K23A (An toàn thông tin)

## Kiến trúc (Commit 1)

```
Internet/User
     |  :80 / :443
 [Nginx Reverse Proxy + SSL]  (frontend_net)
     |               \
     v                v
 [WordPress]      [phpMyAdmin]   (frontend_net + backend_net)
                        |
                        v
                    [MySQL 8.0]  (backend_net - internal)
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

## Git — Commit 1

```powershell
git add .
git commit -m "Commit 1: Deploy wordpress app with Nginx reverse proxy and HTTPS"
git push -u origin main
```
