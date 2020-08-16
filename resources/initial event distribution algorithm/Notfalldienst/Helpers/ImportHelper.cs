using System;
using System.Collections.Generic;
using System.Linq;
using System.Windows.Forms;
using DocumentFormat.OpenXml.Office2010.ExcelAc;
using Notfalldienst.Models;
using Notfalldienst.Statics;

namespace Notfalldienst.Helpers
{
    public static class ImportHelper
    {
        public static string CsvContentToPraxen(string csvContent)
        {
            var response = "";
            if (csvContent.IndexOf("ID;Punkte;Praxis") == 0)
            {
                //erste zeile abschneiden
                csvContent = csvContent.Substring(csvContent.IndexOf("\n") + 1);
                string[] zeilen = csvContent.Split(new char[] { Convert.ToChar("\n") });
                Variables.Praxen = new List<Praxis>();
                Variables.Punkte = new List<Punkt>();
                int i = 0;
                try
                {
                    for (i = 0; i < zeilen.Count(); i++)
                    {
                        if (zeilen[i].Contains(";"))
                        {
                            string[] infos = zeilen[i].Split(new string[] { ";" }, StringSplitOptions.None);
                            if (infos.Count() >= 3)
                            {
                                Praxis p = new Praxis()
                                {
                                    Id = Convert.ToInt32(infos[0].Trim()),
                                    Praxisleiter = infos[2].Trim().Split(new string[] { "," }, StringSplitOptions.None)[0]
                                };
                                for (int a = 0; a < Convert.ToInt32(infos[1].Trim()); a++)
                                {
                                    p.Punkte++;
                                    Variables.Punkte.Add(new Punkt());
                                }
                                Variables.Praxen.Add(p);
                            }
                            else
                            {
                                response += "Zeile " + i + " fehlerhaft\n";
                            }
                        }
                    }
                    response += Variables.Praxen.Count + " Praxen mit insgesamt " + Variables.Punkte.Count() + " Punkten\n";
                }
                catch { response += "Zahl in Zeile " + (i) + " fehlerhaft. Einlesen abgebrochen."; }
                return response;
            }
            else
            {
                response += "Falsches Format, bitte benennen Sie die erste Spalte \"id\", die zweite \"Punkte\" und die Dritte \"Praxen\" (Achtung: Keine Leerschläge!). Das Trennzeichen der .csv Datei muss ein Semikolon sein, bitte überprüfen Sie dies indem Sie die Datei im Editor öffnen.";
                MessageBox.Show(response);
                return null;
            }
        }
    }
}
