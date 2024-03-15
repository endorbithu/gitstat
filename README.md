# Git statisztika userekre vonatkozóan

## Előfeltételek
- php szerver
- bash

## Használat
- A `config.json.EXAMPLE` alapján készítsük el a `config.js`-t.
- futtassuk a `./gitstat.sh` fájlt
- az eredmény az `/output` könyvtárban készül el az egyes projektekre bontva, és a `result.csv` fájlban összesíti

## Módszer
- `git ls-files |	` - Projekt GIT-ben lévő fájljainak összegyüjtése.
- `grep -E 'mappa_neve1/|mappa_neve2/' | ` - (szűkítés) Projektnek mely mappáin belül vizsgálja a fájlokat. Így elkerülhetjük, hogy gyári Laravel stb fájlokat hozzászámítsa.  
- `grep -Ev 'mappa_neve1/|fájlneve|commithash2|' | ` - (szűkítés) Projektnek mely mappáin, konkrét fájl lokáción belül ne vizsgálja a fájlokat. és/vagy mely commitokat ne vizsáljon  
- `grep -E '\.php$|\.phtml$||\.js$|\.vue$|\.ts$|\.css$|\.scss$|\.less$|\.sh$|\.md$|\.py$|\.html$' | ` - (szűkítés) Milyen típusú fájlokat vizsgáljon. Így a kép, csv stb. fájlokat nem számolja bele, ha esetleg bekerültek a gitbe   
- `xargs -n1 git blame -M -C -w -l -f | ` - (Fájlok összegyüjtése kész) A fent meghatározott fájlok sorainak kigyüjtése: minden sor elejére odateszi, hogy mikor és ki adta hozzá/módosította azt a sort (utoljára). Paraméterek: -w ha csak space/enter/tab változás volt az adott soron, az nem számít (pl autoformázás), -M -C ha megvolt már az adott kódrészlet (sorok), csak másolásra/áthelyezésre kerültek, akkor az eredeti usert+dátumot tünteti fel ezeknél a soroknál  
- `grep "2023-" | ` - (szűkítés) Csak a 2023-ban létrehozott VAGY utoljára 2023-ban módosított sorok,  (yyy-mm-dd a dátum forma, lehetséges formák: 2023- , 2023-11, 2023-01-12)
- `grep -E "git_user1|git_user2" ` - (szűkítés) Csak a (git_user1 VAGY git_user2) által módosított sorok (egy embernek lehet több git userneve is, ha több gépről stb dolgozott)  
- `> projekt-userneve.txt ` - Mentés: az összes érintett fájl fent leszűkített sorait egyben a user-neve.txt fájlba menti, hogy vissza lehessen ellenőrizni, nincs-e mégis benne gyári, generált stb. kód  
  
Megjegyzés: Ha egy sort többen is módosítottak, akkor az utolsó módositás dátuma és usere fog csak számítani.  
		
A végén összegyűjti az érintett fájlok listáját, és az érintett commitok listáját is az egyes userekre vonatkozóan, hogy vissza tudjuk ellenőrizni.
