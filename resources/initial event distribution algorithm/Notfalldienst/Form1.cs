using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.IO;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Forms;
using Newtonsoft.Json;
using Notfalldienst.Helpers;
using Notfalldienst.Models;
using Notfalldienst.Statics;

namespace Notfalldienst
{
    public partial class Form1 : Form
    {
        public Form1()
        {
            InitializeComponent();
        }

        public string TierärtzteDatei;
        private void fileopen_Click(object sender, EventArgs e)
        {
            if (openFileDialog1.ShowDialog() == DialogResult.OK)
            {
                try
                {
                    TierärtzteDatei = File.ReadAllText(openFileDialog1.FileName);
                    tierartztepath.Text = openFileDialog1.FileName;
                }
                catch (Exception ex)
                {
                    MessageBox.Show(ex.ToString(), "Es ist ein Fehler aufgetreten");
                }
            }

            CheckIfCanStart();
        }

        public string ScoreJsonDatei;
        private void button2_Click(object sender, EventArgs e)
        {
            if (openFileDialog2.ShowDialog() == DialogResult.OK)
            {
                try
                {
                    ScoreJsonDatei = File.ReadAllText(openFileDialog2.FileName);
                    scorejsonpath.Text = openFileDialog2.FileName;
                }
                catch (Exception ex)
                {
                    MessageBox.Show(ex.ToString(), "Es ist ein Fehler aufgetreten");
                }
            }

            CheckIfCanStart();
        }

        public string HistoryJsonDatei;
        private void button4_Click(object sender, EventArgs e)
        {
            if (openFileDialog2.ShowDialog() == DialogResult.OK)
            {
                try
                {
                    HistoryJsonDatei = File.ReadAllText(openFileDialog2.FileName);
                    historyjsonpath.Text = openFileDialog2.FileName;
                }
                catch (Exception ex)
                {
                    MessageBox.Show(ex.ToString(), "Es ist ein Fehler aufgetreten");
                }
            }

            CheckIfCanStart();
        }

        private void button3_Click(object sender, EventArgs e)
        {
            if (folderBrowserDialog1.ShowDialog() == DialogResult.OK)
            {
                savePath.Text = folderBrowserDialog1.SelectedPath;
                CheckIfCanStart();
            }
        }

        private void textBox2_KeyUp(object sender, KeyEventArgs e)
        {
            CheckIfCanStart();
        }

        private void CheckIfCanStart()
        {
            if (!string.IsNullOrEmpty(savePath.Text) && !string.IsNullOrEmpty(scorejsonpath.Text) &&
                !string.IsNullOrEmpty(historyjsonpath.Text) &&
                !string.IsNullOrEmpty(tierartztepath.Text))
            {
                int res;
                button1.Enabled = int.TryParse(textBox2.Text, out res);
            }
            else
                button1.Enabled = false;
        }

        /// <summary>
        /// start
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private async void button1_Click(object sender, EventArgs e)
        {
            



            richTextBox2.Text = "";
            if (Variables.Punkte.Count == 0)
            {
                var response = ImportHelper.CsvContentToPraxen(TierärtzteDatei);
                if (response == null)
                {
                    button1.Enabled = true;
                    return;
                }

                richTextBox2.Text += response;

                try
                {
                    var count = 0;
                    var praxisdic = SaveHelper.RetrieveScore(ScoreJsonDatei);

                    foreach (var key in praxisdic.Keys)
                    {
                        var praxis = Variables.Praxen.FirstOrDefault(p => p.Id == key);
                        if (praxis != null)
                        {
                            count++;
                            praxis.LastYearScore = praxisdic[key];
                        }
                    }

                    richTextBox2.Text += count + " Praxen konnten ein Score zugeordnet werden\n";
                }
                catch (Exception ex)
                {
                    var message =
                        "score.json Datei fehlerhaft, das Einlesen musste abgebrochen werden. \nGenauer Fehler: " + ex;
                    MessageBox.Show(message);
                    richTextBox2.Text += message;
                    return;
                }

                try
                {
                    Variables.History = SaveHelper.RetrieveHistory(HistoryJsonDatei);
                    richTextBox2.Text += "history.json hat " + Variables.History.Count + " Einträge\n";
                }
                catch (Exception ex)
                {
                    var message =
                        "history.json Datei fehlerhaft, das Einlesen musste abgebrochen werden. \nGenauer Fehler: " + ex;
                    MessageBox.Show(message);
                    richTextBox2.Text += message;
                    return;
                }
            }

            //init
            Variables.Minanzahl = new int[4] { 0, 0, 0, 0 };

            //Feiertage werden eingelesen
            string[] daten = this.feiertage.Text.Split(new char[] { Convert.ToChar("\n") });
            for (int i = 0; i < daten.Count(); i++)
            {
                if (daten[i].Count() > 2)
                {
                    try
                    {
                        Variables.Feiertage.Add(Convert.ToDateTime(daten[i].Trim()));
                    }
                    catch
                    {
                        MessageBox.Show("Feiertag auf der Zeile " + (i + 1) + " konnte nicht eingelesen werden");
                        return;
                    }
                }
            }


            DateTime start = Start.Value.Date;
            DateTime ende = Ende.Value.Date;
            if (ende - start < TimeSpan.FromDays(0))
            {
                MessageBox.Show("Bitte wählen Sie gültige Anfangs- und Enddaten");
                string.Format("hallo {0}!", "ww");
                return;
            }

            int normaldays = 0;
            int saturdays = 0;
            int sundays = 0;
            int holidays = 0;

            //bestimmung anzahl der jeweiligen gruppe
            while (start < ende)
            {
                if (DateTimeHelper.IsFeiertag(start))
                    holidays++;
                else if (start.DayOfWeek == DayOfWeek.Saturday)
                    saturdays++;
                else if (start.DayOfWeek == DayOfWeek.Sunday)
                    sundays++;
                else
                    normaldays++;
                start = start.AddDays(1);
            }
            int totaldays = holidays + normaldays + saturdays + sundays;
            richTextBox2.Text += totaldays + " Tage zu verteilen\n";



            //feiertage
            VerteilerHelper.VerteileTageZuPunkte(holidays, 0);
            Variables.Punkte = Variables.Punkte.OrderBy(d => d.Score).ToList();

            //sonntag
            VerteilerHelper.VerteileTageZuPunkte(sundays, 1);
            Variables.Punkte = Variables.Punkte.OrderBy(d => d.Score).ToList();

            //samstag
            VerteilerHelper.VerteileTageZuPunkte(saturdays, 2);
            Variables.Punkte = Variables.Punkte.OrderBy(d => d.Score).ToList();

            //wochentag
            VerteilerHelper.VerteileTageZuPunkte(normaldays, 3);
            Variables.Punkte = Variables.Punkte.OrderByDescending(d => d.Score).ToList();

            //sotiere nahc feiertag, damit niemand zwei hat
            Variables.Punkte = Variables.Punkte.OrderByDescending(p => p.Anzahl[0]).ToList();

            var firsttime = true;
            //Punkte an Praxen verteilen
            while (Variables.Punkte.Count > 0)
            {
                if (!firsttime)
                    Variables.Punkte = Variables.Punkte.OrderByDescending(d => d.Score).ToList();
                else
                    firsttime = false;

                //Punkte nach score sortieren (highest -> last)
                Variables.Praxen = Variables.Praxen.OrderBy(p => p.ScorePerPoint).ToList();

                //Punkte einfügen
                for (int i = 0; i < Variables.Praxen.Count; i++)
                {
                    if (Variables.Praxen[i].Punkte > Variables.Praxen[i].AssignedPoints.Count)
                    {
                        Variables.Punkte[0].Praxis = Variables.Praxen[i];
                        Variables.Praxen[i].AssignedPoints.Add(Variables.Punkte[0]);
                        Variables.Punkte.RemoveAt(0);
                    }
                }
            }

            //beste Verteilung (einmalig)
            List<Punkt> punkteliste = new List<Punkt>();
            foreach (var item in Variables.Praxen)
            {
                int count = item.AssignedPoints.Count;
                int totalcount = punkteliste.Count;
                int step = totalcount / count;
                count--;
                while (true)
                {
                    punkteliste.Insert(totalcount, item.AssignedPoints[count]);
                    count--;
                    totalcount -= step;
                    if (count <= -1)
                    {
                        break;
                    }
                }
            }


            start = Start.Value.Date;
            ende = Ende.Value.Date;

            //daten verteilen
            List<Punkt> punkte = CloneHelper.DeepClone(punkteliste);

            var tries = 0;
            var maxtries = Convert.ToInt32(triesproabstand.Text);
            var minabst = Convert.ToInt32(minabstand.Text);

            richTextBox2.Text += "versuche mit mindestabstand " + minabst + "\n";
            var richtextboxtext = richTextBox2.Text;
            bool found = false;
            while (!found)
            {
                found = await Task.Run(() => VerteilerHelper.VerteileNotfalldienstPunkteZuDaten(punkte, start, ende,
                    Variables.History, minabst));

                if (!found)
                {
                    punkte = CloneHelper.DeepClone(punkteliste);
                    ThreadSafeRandom.Shuffle(punkte);
                    if (++tries > maxtries)
                    {
                        tries = 0;
                        minabst--;
                        if (minabst == 0)
                        {
                            MessageBox.Show(
                                "Vorgang kann nicht abgeschlossen werden. Die Punkte können nicht gemäss den Regeln verteilt werden. Durch das Zufallsprinzip kann das vorkommen, bitte versuchen Sie es einfach nocheinmal.");
                            return;
                        }
                        richTextBox2.Text += "\nversuche mit mindestabstand " + minabst + "\n";
                        richtextboxtext = richTextBox2.Text;
                    }
                    else
                        richTextBox2.Text = richtextboxtext + "gescheitert für mindestabstand " + minabst + " (" + tries + " / " + maxtries + ")\n";
                }
            }

            richTextBox2.Text += "\n\nLösung gefunden!\n";
            richTextBox2.Text += "mindestabstand: " + minabst + " Tage\n";


            //add correct points to praxen
            foreach (var praxise in Variables.Praxen)
            {
                praxise.AssignedPoints = punkte.Where(p => p.Praxis == praxise).ToList();
            }

            VerteilerHelper.VerteileWochentelefone(Variables.Praxen.OrderBy(p => p.Id).ToList(), start, ende, Convert.ToInt32(textBox2.Text.Trim()));
            
            double tiefstes = Variables.Praxen.Where(d => d.Punkte > 0).OrderBy(d => d.ScorePerPoint).First().ScorePerPoint;
            double durchschnitt = Variables.Praxen.Sum(d => d.ScorePerPoint * d.Punkte) / Variables.Praxen.Sum(p => p.Punkte);
            double höchstens = Variables.Praxen.Where(d => d.Punkte > 0).OrderByDescending(d => d.ScorePerPoint).First().ScorePerPoint;

            richTextBox2.Text += "tiefstes: " + tiefstes + "\n";
            richTextBox2.Text += "höchstens: " + höchstens + "\n";
            richTextBox2.Text += "durchschnitt: " + Math.Round(durchschnitt, 3) + "\n";

            richTextBox2.Text += "dateien werden generiert...\n";

            ExportHelper.ExportStats(savePath.Text);

            var dic = Variables.Praxen.ToDictionary(praxis => praxis.Id, praxis => praxis.ScorePerPoint * praxis.Punkte - (praxis.Punkte * durchschnitt));
            ExportHelper.ExportSave(dic, savePath.Text);

            ExportHelper.ExportSql(punkte, Variables.Praxen, savePath.Text);
            ExportHelper.ExportAnalythicsAndHistory(punkte, savePath.Text);

            richTextBox2.Text += "erfolgreich abgeschlossen";

        }
    }
}
