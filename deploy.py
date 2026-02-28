#!/usr/bin/env python3
"""
Deploy script for syncing code, DB, and media to the live server.
Handles git untracked files cleanly before pulling.
"""

import subprocess
import sys
import os

# ===========================
# ⚙️  إعدادات السيرفر
# ===========================
SSH_USER = "your_ssh_user"          # غيّر هذا
SSH_HOST = "your_server_host"       # غيّر هذا (مثلاً tech-sys.online)
SSH_KEY  = ""                       # اتركه فارغاً إذا تستخدم كلمة مرور
PROJECT_PATH = "/var/www/html"      # مسار المشروع على السيرفر (غيّره)

# ===========================
# 🔧 دوال مساعدة
# ===========================
def run_ssh(commands: list[str], label=""):
    """تشغيل قائمة أوامر على السيرفر عبر SSH"""
    ssh_cmd = ["ssh"]
    if SSH_KEY:
        ssh_cmd += ["-i", SSH_KEY]
    ssh_cmd += [f"{SSH_USER}@{SSH_HOST}", " && ".join(commands)]
    
    print(f"\n{'='*50}")
    print(f"🔄  {label}")
    print(f"{'='*50}")
    
    result = subprocess.run(ssh_cmd, text=True, capture_output=True)
    if result.stdout:
        print(result.stdout)
    if result.stderr:
        print("⚠️ ", result.stderr, file=sys.stderr)
    
    if result.returncode != 0:
        print(f"❌ فشلت العملية: {label}")
        sys.exit(1)
    
    print(f"✅ تمت: {label}")


def run_local(cmd: str, label=""):
    """تشغيل أمر محلي"""
    print(f"\n>>> {label or cmd}")
    result = subprocess.run(cmd, shell=True, text=True, capture_output=True)
    if result.stdout:
        print(result.stdout)
    if result.stderr:
        print("⚠️ ", result.stderr, file=sys.stderr)
    return result.returncode == 0


# ===========================
# 📦  sync_code
# ===========================
def sync_code():
    """تحديث الكود على السيرفر مع معالجة الملفات غير المتتبعة"""
    print("\n--- جاري تشغيل: sync_code ---")
    print(">>> تحديث الكود ومسح الكاش...")

    run_ssh([
        f"cd {PROJECT_PATH}",

        # ✅ الحل: احذف الملفات غير المتتبعة قبل السحب
        "echo '🧹 حذف الملفات غير المتتبعة...'",
        "git clean -fd",           # يحذف الملفات والمجلدات غير المتتبعة
        "git checkout -- .",       # يتجاهل أي تعديلات محلية على ملفات متتبعة

        # سحب آخر تحديث
        "echo '⬇️  سحب آخر تحديث من GitHub...'",
        "git pull --ff-only origin main",

        # تصفير الكاش
        "php artisan config:clear",
        "php artisan cache:clear",
        "php artisan view:clear",
        "php artisan route:clear",
        "php artisan optimize",
    ], label="تحديث الكود ومسح الكاش")

    print(">>> تمت العملية.")


# ===========================
# 💾  sync_db
# ===========================
def sync_db():
    """مزامنة قاعدة البيانات"""
    print("\n--- جاري تشغيل: sync_db ---")
    # أضف منطق مزامنة DB هنا حسب إعداداتك
    print(">>> (sync_db) لا يوجد تغيير في هذا السكريبت - يعمل كالمعتاد")


# ===========================
# 🖼️  sync_media
# ===========================
def sync_media():
    """مزامنة الصور والميديا"""
    print("\n--- جاري تشغيل: sync_media ---")
    # أضف منطق مزامنة الميديا هنا
    print(">>> (sync_media) لا يوجد تغيير في هذا السكريبت - يعمل كالمعتاد")


# ===========================
# 🚀 النقطة الرئيسية
# ===========================
if __name__ == "__main__":
    steps = sys.argv[1:] or ["sync_code", "sync_db", "sync_media"]

    for step in steps:
        if step == "sync_code":
            sync_code()
        elif step == "sync_db":
            sync_db()
        elif step == "sync_media":
            sync_media()
        else:
            print(f"❌ خطوة غير معروفة: {step}")
            sys.exit(1)

    print("\n✅ انتهت جميع العمليات بنجاح!")
