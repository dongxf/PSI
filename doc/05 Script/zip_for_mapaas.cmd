"C:\Program Files\Git\git-bash" clone_PSI.sh

cd D:\temp\PSI

if exist PSI.zip  del PSI.zip /q

"C:\Program Files\WinRAR\rar" a -r -ep1 PSI.zip "D:\temp\PSI\*"

pause