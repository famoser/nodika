using System;
using System.Collections.Generic;
using System.Data;
using System.IO;
using System.Linq;
using System.Reflection;
using System.Text;
using System.Threading.Tasks;
using Notfalldienst.Models;
using Notfalldienst.Statics;

namespace Notfalldienst.Helpers
{
    public static class ExportHelper
    {
        public static void ExportStats(string folder)
        {
            DataTable dt = new DataTable();
            dt.Columns.Add("Praxisnummer");
            dt.Columns.Add("Punkte angefordert / zugewiesen");
            dt.Columns.Add("Feiertage");
            dt.Columns.Add("Sonntage");
            dt.Columns.Add("Samstage");
            dt.Columns.Add("Wochentage");
            dt.Columns.Add("Wochendienste");
            dt.Columns.Add("Gutschrift von 2015");
            dt.Columns.Add("Effektiv pro Punkt");
            dt.Columns.Add("Total pro Punkt");
            dt.Columns.Add("Gutschrift 2016");

            dt.Rows.Add("");

            var durchschnitt = Variables.Praxen.Sum(p => p.ScorePerPoint * p.Punkte) / Variables.Praxen.Sum(p => p.Punkte);
            var praxen = Variables.Praxen.OrderBy(p => p.Id);
            foreach (var praxis in praxen)
            {
                dt.Rows.Add(praxis.Id, praxis.Punkte + " / " + praxis.AssignedPoints.Count,
                    praxis.AssignedPoints.Sum(d => d.Anzahl[0]),
                    praxis.AssignedPoints.Sum(d => d.Anzahl[1]),
                    praxis.AssignedPoints.Sum(d => d.Anzahl[2]),
                    praxis.AssignedPoints.Sum(d => d.Anzahl[3]),
                    praxis.Wochentelefone.Count,
                    praxis.LastYearScore,
                    praxis.AbsoluteScorePerPoint,
                    praxis.ScorePerPoint,
                    (praxis.ScorePerPoint - durchschnitt) * praxis.Punkte);
            }

            dt.Rows.Add("");
            dt.Rows.Add("");
            dt.Rows.Add("Total",
                Variables.Praxen.Sum(p => p.Punkte),
                Variables.Praxen.Sum(p => p.AssignedPoints.Sum(pu => pu.Anzahl[0])),
                Variables.Praxen.Sum(p => p.AssignedPoints.Sum(pu => pu.Anzahl[1])),
                Variables.Praxen.Sum(p => p.AssignedPoints.Sum(pu => pu.Anzahl[2])),
                Variables.Praxen.Sum(p => p.AssignedPoints.Sum(pu => pu.Anzahl[3])),
                "");

            dt.Rows.Add("");
            dt.Rows.Add("");
            dt.Rows.Add("Statistik");
            dt.Rows.Add("Mittelwert Punkte", Variables.Praxen.Sum(p => p.ScorePerPoint * p.Punkte) / Variables.Praxen.Sum(p => p.Punkte));
            ExcelHelper.CreateExcelDocument(dt, Path.Combine(folder, "stats.xlsx"));
        }

        public static void ExportSave(Dictionary<int, double> dic, string folder)
        {
            var str = SaveHelper.SaveScore(dic);
            File.WriteAllText(Path.Combine(folder, "score.json"), str);
        }

        public static void ExportSql(List<Punkt> notfalldienste, List<Praxis> wochentelefone, string folder)
        {
            var sql = new List<string> { "INSERT INTO termine (datum,user_nr,datum_type) VALUES " };
            foreach (var punkt in notfalldienste)
            {
                foreach (var date in punkt.Dates)
                {
                    sql.Add("('" + date.Key.ToString("yyyy-MM-dd") + "', '" + punkt.Praxis.Id + "','" + (date.Value + 1) + "'),");
                }
            }
            sql[sql.Count - 1] = sql[sql.Count - 1].Substring(0, sql[sql.Count - 1].Length - 1);
            File.WriteAllLines(Path.Combine(folder, "notfalldienste.sql"), sql);

            var sql2 = new List<string> { "INSERT INTO wochentelefon (startdatum,user_nr) VALUES " };
            foreach (var praxis in wochentelefone)
            {
                foreach (var dateTime in praxis.Wochentelefone)
                {
                    sql2.Add("('" + dateTime.ToString("yyyy-MM-dd") + "', '" + praxis.Id + "'),");
                }
            }
            sql2[sql2.Count - 1] = sql2[sql2.Count - 1].Substring(0, sql2[sql2.Count - 1].Length - 1);
            File.WriteAllLines(Path.Combine(folder, "wochentelefon.sql"), sql2);
        }

        public static void ExportAnalythicsAndHistory(List<Punkt> punkte, string folder)
        {
            List<int> historyJson = new List<int>();

            DataTable dt = new DataTable();
            dt.Columns.Add("Datum");
            dt.Columns.Add("Type");
            dt.Columns.Add("Praxis");
            
            dt.Rows.Add("");

            var alldates = new Dictionary<DateTime, int[]>();
            var byPrax = new Dictionary<int, List<Punkt>>();
            foreach (var punkt in punkte)
            {
                if (!byPrax.ContainsKey(punkt.Praxis.Id))
                    byPrax.Add(punkt.Praxis.Id,new List<Punkt>());
                byPrax[punkt.Praxis.Id].Add(punkt);

                foreach (var date in punkt.Dates)
                {
                    alldates.Add(date.Key, new [] { date.Value, punkt.Praxis.Id });
                }
            }
            alldates = alldates.OrderBy(d => d.Key).ToDictionary(d => d.Key, t => t.Value);

            foreach (var strings in alldates)
            {
                historyJson.Add(strings.Value[1]);
                DataRow drow = dt.NewRow();
                drow["Datum"] = strings.Key.ToString("dd. MM. yyyy");
                drow["Type"] = strings.Value[0];
                drow["Praxis"] = strings.Value[1];
                dt.Rows.Add(drow);
            }

            var filestr = SaveHelper.SaveHistory(historyJson);
            File.WriteAllText(Path.Combine(folder, "history.json"), filestr);


            dt.Rows.Add("");
            dt.Rows.Add("Nach Praxis");
            dt.Rows.Add("Datum", "Typ");

            foreach (var dicentry in byPrax)
            {
                dt.Rows.Add(dicentry.Key);

                var dates = new Dictionary<DateTime, string>();
                
                foreach (var punkt in dicentry.Value)
                {
                    foreach (var date in punkt.Dates)
                    {
                        dates.Add(date.Key, date.Value.ToString());
                    }
                }
                dates = dates.OrderBy(d => d.Key).ToDictionary(d => d.Key, t => t.Value);

                foreach (var strings in dates)
                {
                    dt.Rows.Add(strings.Key.ToString("dd. MM. yyyy"), strings.Value);
                }
                dt.Rows.Add("");
            }
            ExcelHelper.CreateExcelDocument(dt, Path.Combine(folder,"analyze.xlsx"));
        }
    }
}
