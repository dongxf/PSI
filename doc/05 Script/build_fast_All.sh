# 删除旧文件
echo 'Step 1: delete old repo and clone new repo'
cd /d/temp
rm -rf /d/temp/PSI

# clone PSI
git clone https://gitee.com/crm8000/PSI.git

# 删除不用发布的文件
rm -rf /d/temp/PSI/doc
rm -rf /d/temp/PSI/.git
rm -rf /d/temp/PSI/static
rm -rf /d/temp/PSI/m.html
rm -rf /d/temp/PSI/service-worker.js

echo 'Step 2: building PSI Mobile'
cd /d/temp/PSI_Mobile
git pull
npm run build

# 复制移动端文件到PSI主目录
cp /d/temp/PSI_Mobile/dist/index.html /d/temp/PSI/m.html
cp /d/temp/PSI_Mobile/dist/service-worker.js /d/temp/PSI
mkdir /d/temp/PSI/static
cp -r /d/temp/PSI_Mobile/dist/static/* /d/temp/PSI/static

echo 'Done! Have fun!'
