# Discord Among Us Codes Bot

Ez egy Discord bot PHP-ban megírva Among Us szerverekhez, ami egy fix publikus üzenetbe gyűjti a felhasználók által megosztott Among Us szerver kódokat.

![Sample](https://i.imgur.com/coShwyh.png)

## Miért készítettem

Amikor Discord szervereken emberek próbálnak játszani Among Us-t egymással, mindig kellemetlen szokott lenni megadni egymásnak a kódokat. Vagy folyamatosan kérdezik újonnan belépett játékosok a voice csatornában, hogy mi a kód (amit folyamatosan diktálnak a többiek), vagy keresgélni kell a kódot a kódmegosztó csatornán, hogy melyik a legújabb beírt kód valaki által, aki a játék voice csatornájában benne van.

Ezért szerettem volna írni egy ilyet botot, ami segítene egy helyre gyűjteni a kódokat, hogy ne kelljen keresgetni.

Csak hobbi célból készítettem, mint web fejlesztő.

## Kérdések és válaszok

### Hogyan működik?

A szerveredhez meg lenne jelölve:

1. Egy forrás csatorna (játékos/csapat-kerső szoba)
2. Egy (új) cél csatorna, ahova a bot for írni
3. Logika, amiből megtudja, melyek a játék és nem játék voice csatornák.

Emberek irogatnák továbbra is a kódmegosztásokat az 1-es számban megjelölt szobában. A bot megfigyeli, hogy aki üzenetet ír abba a szobába, hogy ugye benne vannak egy 3-masban megadott logika alapján egy játék szobában, és hogy amit írtak - üzenetet - abban található-e Among Us szerver kódnak kinéző "szó" (pl HLESPO), és ha igen, akkor feljegyzi a bot, hogy ahhoz a voice csatornához (most már) az a kód tartozik.

Azután a 2-es számú csatornában editálja a saját üzenetét, hogy benne legyen az összes jelenlegi kód. Az üzenet valahogy úgy nézne ki, mint a példa kép följebb.

Így a felhasználók továbbra is az 1-es pontban megjelölt szobába osztogatnák meg a kódjaikat, viszont a 2-es számú szobából lesz érdemes kiolvasniuk őket.

### Akkor most át kellene szokniuk az embereknek, hogy hogyan osztanak meg kódot?

Nagyon minimálisan. Direkt okosra írtam meg a botot, hogy a szobában - ahol eddig is osztottak meg az emberek kódokat - elválassza a bot az emberek üzeneteiből a rizsát, és megtartsa a lényeget: a kódot, és opcionálisan, hogy NA/EU.

Ilyen rizsa szöveggel teli kommentekből is megtalálja a bot a lényeget: EXLDBF a kód, és NA a szerver. 

![Rizsa példa](https://i.imgur.com/bk4YyV4.png)

A leglényegesebb dolog, hogy fontos lesz egyrészt mind nagybetűkkel írni a kódot (különben lehetetleg megkülönböztetni sima szavaktól). Illetve muszáj egy üzenetbe írni mind a kódot, mind a szervert; nem lehet egymás alatt különböző üzenetekbe, különben a bot csak a kódot fogja látni.

De akkor is legrosszabb esetben továbbra is az eddigi kódmegosztó csatornából az eddig megszokott módon manuálisan is megtalálhatják a játékosok a kódokat.

### Mi van, ha emberek trollkodnak, és át akarják írni mások kódjait?

A bot nézi, hogy melyik voice csatornában van az, aki a kódot írja, ezért nem tudod felülírni annak a kódját, akinek a voice csatornájában nem vagy. Tehát megtelt 10 fős voice csatornát nem lehetséges külsősnek trollkodnia.

Persze beléphetne valaki egy be nem telt voice csatornára csak azért, hogy egy kamu kódot írjon, felülírván a kódot a voice csatornához, de ilyen fajta trollkodásokat mások is csinálhattak volna eddig is a megszokott manuális megosztási rendszerben.

Még annyi, hogy ha valaki olyan voice csatornán van, ami nem is Among Us játékhoz tartozik, akkor ignorálja a kódodat a bot. Illetve 30 másodpercenként vizsgálja a bot, hogy kiürült-e egy voice csatorna, amihez volt elmentett kód, és ha igen, kitörli a kódot hozzá; szóval azt se tudják megtrollkodni (maradandóan), hogy bejárogassanak az összes voice szobába, hogy valami kamu kódot írjanak oda.

### Milyen jogosultságok kellenek a botnak?

Semmi más azon kívül, hogy csinálnod kell egy dedikált szöveges csatornát - ahova csak a bot tud írni - ahogy ugyanazt az egy üzenetet fogja fölülirogatni a kód táblázattal.

Privát üzeneteket se küld a bot, szóval letilthatod ezt a jogot neki, ha akarod.

### Hogyan tudom behívni ezt a botot a szerveremre?

Szólj nekem Discordon vagy emailben. amcsi néven futok, vagy attila kukac szeremi pont org az email címem.

Nem tudod csak úgy magadtól behívni a botot a szerveredre, mert kell némi dolgot manuálisan konfigurálnom a szerveredhez: hogy melyik a forrás csatorna (ahol a játékosok megosztanák a kódokat), melyik a cél csatorna (ahova a bot gyűjti a kódokat), és a logikát, hogy megkülönböztesse az Among Us játék voice szobákat a többi voice szobától.

## Rólam

Szerémi Attila (aka. amcsi) vagyok, 1987-ben születtem, és 2007-2009 óta vagyok web programozó (professzionálisan).
Ezen a honlapomon olvashattok rólam: https://www.szeremi.org/
