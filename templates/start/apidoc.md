WIAG API
========

Das Application Programming Interface (API) für WIAG ermöglicht die automatisierte
Abfrage von Daten aus dem WIAG Datenbestand. Die Daten werden als ein
[JSON](https://www.json.org/json-de.html)- oder CSV-Dokument ausgeliefert.

### Einzelabfrage
Mit der Angabe einer WIAG-Kennung erhält man alle Elemente eines Datensatzes, zum Beispiel zu einer Person. Die URL hat folgenden Aufbau: `BaseUrl/ID?format=json` oder `BaseUrl/ID?format=csv`.

Beispiel:  
{{ url('api_bishop', {wiagidlong: 'WIAG-Pers-EPISCGatz-10076-001', format: 'json'}) }}  
{{ url('api_bishop', {wiagidlong: 'WIAG-Pers-EPISCGatz-10076-001', format: 'csv'}) }}

#### Struktur
Das JSON-Dokument enthält ein Element `person`, das die einzelnen Angaben zu der
Person umfasst. Dazu gehört auch eine Liste von Ämtern im Element `offices` und
meistens eine Gruppe von externen Kennungen im Element `identifiers`.

Beispiel:
``` json
{"person":
 {"wiagId":"WIAG-Pers-EPISCGatz-10076-001",
  "familyName":"Braida",
  "givenName":"Franz Julian",
  "prefix":"Graf von",
  "comment_person":"Ep. tit. Hipponensis",
  "dateOfBirth":"1654",
  "dateOfDeath":"1727",
  "identifier":
  {"viafId":"5652149719115111130002",
   "wikidataId":"Q12017135"},
  "offices":[
	  {"officeTitle":"Weihbischof",
	   "diocese":"Olmütz",
	   "dateStart":"1703",
	   "dateEnd":"1727"},
	  {"officeTitle":"Generalvikar",
	   "diocese":"Olmütz",
	   "dateStart":"1703",
	   "dateEnd":"1727"}],
  "reference":
  {"title":"Die Bischöfe des Heiligen Römischen Reiches 1648 bis 1803",
   "author":"Gatz, Erwin",
   "short":"Gatz, Bischöfe 1648 bis 1803",
   "pages":"41"
  }
 }
}
```

Das CSV Dokument ist ein UTF-8-Text. Die erste Zeile enthält die Feldbezeichner. Die folgende Zeile die Feldwerte. Die Feldinhalte einer Zeile sind durch Tabulator voneinander getrennt.

Beispiel:
``` text
person.wiagId	person.familyName	person.givenName	person.prefix	person.comment_person	...
WIAG-Pers-EPISCGatz-10076-001	Braida	"Franz Julian"	"Graf von"	"Ep. tit. Hipponensis"	...

```

### Suchanfrage
Mit der Angabe von Suchparametern erhält man alle Datensätze, die der Suchanfrage
entsprechen. Gesucht werden kann nach folgenden Eigenschaften:

- **name**: Finde Übereinstimmugen in Vorname, Nachname, Namenspräfix, Varianten des
  Vornames und Varianten des Nachnamens.
  Beispiele: `josef`, `graf`,
  `gondo`, `Franz Josef Graf von Gondola`
- **diocese**: Finde Übereinstimmugen in den Name der Bistümer, in denen die Person ein
  Amt innehatte.
  Beispiele: `burg`, `würzburg`.
- **office**: Finde Übereinstimmungen in den Amtsbezeichnungen.
  Beispiele: `vikar`,
  `administrator`.
- **year**: Finde Übereinstimmungen für einen Zeitraum von plus/minus 50 Jahren zu der
  angegebenen Jahreszahl. Berücksichtigt wird der größte Zeitraum, der sich ergibt
  aus Geburtsdatum, Sterbedaten, Amtsbeginn und Amtsende.
- **someid**: Finde eine exakte Übereinstimmungen mit einer Kennung für eine Person in
  folgenden Verzeichnissen:
  - [WIAG]({{ url('wiag_welcome') }})
  - [Gemeinsame Normdatei (GND)]({{ urlgnd }})
  - [Virtual International Authority File (VIAF)]({{ urlviaf }})
  - [Wikidata]({{ urlwikidata }})
  - [Personendatenbank der Germania Sacra]({{ urlgspersons }})

Die Suchparameter sind logisch UND-verknüpft: Es werden nur solche Datensätze angezeigt, für die alle Parameter/Wert-Kombinationen zutreffen.
Die Suchparameter werden an die URL jeweils mit dem Schlüsselwort angehängt. JSON ist das Standard-Format, d.h. hier kann die Angabe des Formats entfallen:
`BaseUrl?key1=valuet&key2=value&format=[json|csv]`

Beispiele (JSON):  
{{ url('api_query_bishops', {name: 'gondo', format: 'json'})|raw }}  
{{ url('api_query_bishops', {name: 'Hohenlohe', diocese: 'Bamberg'})|raw }}  
{{ url('api_query_bishops', {diocese: 'Trier', year: '1450', format: 'json'})|raw }}  
{{ url('api_query_bishops', {someid: 'WIAG-Pers-EPISCGatz-3302-001', format: 'json'})|raw }}  
{{ url('api_query_bishops', {someid: 'Q1506604'})|raw }}

Beispiele (CSV):  
{{ url('api_query_bishops', {name: 'gondo', format: 'csv'})|raw }}  
{{ url('api_query_bishops', {name: 'Hohenlohe', diocese: 'Bamberg', format: 'csv'})|raw }}  
{{ url('api_query_bishops', {diocese: 'Trier', year: '1450', format: 'csv'})|raw }}  
{{ url('api_query_bishops', {someid: 'WIAG-Pers-EPISCGatz-3302-001', format: 'csv'})|raw }}  
{{ url('api_query_bishops', {someid: 'Q1506604', format: 'csv'})|raw }}

#### Struktur
Das JSON-Dokument enthält ein Element `persons`, mit den Kindern `count` (Anzahl der Datensätze) und `list` (Liste der Datensätze).

Beispiel:
```json
{"persons":
 {"count":22,
  "list":
  [{"person":
	{"wiagId":"WIAG-Pers-EPISCGatz-3302-001",
	 "familyName":"Hohenlohe",
	 ...
	}
	"person":
	{"wiagId":"WIAG-Pers-EPISCGatz-21477-001",
	 "familyName":"Hohenlohe",
	 ...
	}
	...
   }
  ]
 }
}

```

Das CSV Dokument ist ein UTF-8-Text. Die erste Zeile enthält die Feldbezeichner. Die folgende Zeile die Feldwerte. Die Feldinhalte einer Zeile sind durch Tabulator voneinander getrennt.

Beispiel:
```text
person.wiagId	person.familyName	person.givenName	person.variantFamilyName	person.comment_name	person.prefix	person.comment_person	...
WIAG-Pers-EPISCGatz-3627-001	"Falkenstein und Königstein"	Werner			von		...
WIAG-Pers-EPISCGatz-21476-001	Aldendorf	Konrad			von	"Ep. tit. Azotensis"	...
WIAG-Pers-EPISCGatz-21477-001	Eydel	Tilman			von	"Ep. tit. Azotensis ?"	...
WIAG-Pers-EPISCGatz-3673-001	Blankenheim	Friedrich			von		...
WIAG-Pers-EPISCGatz-21281-001	"Franqueloy de Vico"	Joannes				"Ep. tit. Taurisiensis"	....
```
{# do not cleanup whitespaces #}
