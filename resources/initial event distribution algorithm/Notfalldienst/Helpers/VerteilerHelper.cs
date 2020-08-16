using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Notfalldienst.Models;
using Notfalldienst.Statics;

namespace Notfalldienst.Helpers
{
    public static class VerteilerHelper
    {
        public static void VerteileTageZuPunkte(int tage, int index)
        {
            //verteilung feiertage (sofern für jeden mindestens einen zur Verfügung steht)
            double durchschnitt = tage / Variables.Punkte.Count;
            while (durchschnitt >= 1)
            {
                Variables.Minanzahl[index]++;
                foreach (var item in Variables.Punkte)
                {
                    item.Anzahl[index] += 1;
                    tage--;
                }
                durchschnitt = tage / Variables.Punkte.Count;
            }

            //verteilung des restes
            for (int i = 0; i < Variables.Punkte.Count && tage > 0; i++)
            {
                Variables.Punkte[i].Anzahl[index]++;
                Variables.Punkte[i].Mehr[index]++;

                tage--;
            }
        }

        public static bool VerteileNotfalldienstPunkteZuDaten(List<Punkt> punkte, DateTime start, DateTime end, List<int> lastNotfalldienst, int minabstand)
        {
            var position = -1;
            var abstand = new List<int>();
            for (int i = 0; i < minabstand; i++)
            {
                if (lastNotfalldienst.Count > i)
                    abstand.Add(lastNotfalldienst[i]);
                else
                    abstand.Add(-1);
            }

            foreach (var punkt in punkte)
            {
                punkt.TempAnzahl = new[] {punkt.Anzahl[0], punkt.Anzahl[1], punkt.Anzahl[2], punkt.Anzahl[3]};
            }

            bool stepDate = true;
            var lastStartPos = -1;
            while (start < end)
            {
                position++;
                if (position >= punkte.Count)
                    position = 0;

                var startpos = position;
                if (!stepDate)
                {
                    if (lastStartPos == position)
                        return false;

                    startpos = lastStartPos;
                }
                else
                    lastStartPos = startpos;

                while (abstand.Any(a => a == punkte[position].Praxis.Id))
                {
                    position++;
                    if (position >= punkte.Count)
                        position = 0;

                    if (startpos == position)
                        return false;
                }

                stepDate = false;
                if (DateTimeHelper.IsFeiertag(start))
                {
                    if (punkte[position].TempAnzahl[0] > 0)
                    {
                        punkte[position].TempAnzahl[0]--;
                        punkte[position].Dates.Add(start, 0);
                        stepDate = true;
                    }
                }
                else if (start.DayOfWeek == DayOfWeek.Sunday)
                {
                    if (punkte[position].TempAnzahl[1] > 0)
                    {
                        punkte[position].TempAnzahl[1]--;
                        punkte[position].Dates.Add(start, 1);
                        stepDate = true;
                    }
                }
                else if (start.DayOfWeek == DayOfWeek.Saturday)
                {
                    if (punkte[position].TempAnzahl[2] > 0)
                    {
                        punkte[position].TempAnzahl[2]--;
                        punkte[position].Dates.Add(start, 2);
                        stepDate = true;
                    }
                }
                else
                {
                    if (punkte[position].TempAnzahl[3] > 0)
                    {
                        punkte[position].TempAnzahl[3]--;
                        punkte[position].Dates.Add(start, 3);
                        stepDate = true;
                    }
                }

                if (stepDate)
                {
                    start = start.AddDays(1);
                    abstand.Insert(0, punkte[position].Praxis.Id);
                    abstand.RemoveAt(abstand.Count - 1);
                }
            }

            return true;
        }

        public static void VerteileWochentelefone(List<Praxis> praxen, DateTime start, DateTime end, int lastWochentelefon)
        {
            var position = -1;
            start = DateTimeHelper.GetNextMonday(start);

            var lastPrax = praxen.FirstOrDefault(p => p.Id == lastWochentelefon);
            if (lastPrax != null)
                position = praxen.IndexOf(lastPrax);

            if (position >= praxen.Count - 1)
                position = 0;
            if (praxen[position + 1].Id == lastWochentelefon)
                position++;

            List<int> forwardedPositions = new List<int>();
            while (start < end)
            {
                for (int index = 0; index < forwardedPositions.Count; index++)
                {
                    var forwardedPosition = forwardedPositions[index];
                    if (!praxen[forwardedPosition].AssignedPoints.Any(p => p.Dates.Any(d => d.Key >= start && d.Key <= start.AddDays(7))))
                    {
                        praxen[forwardedPosition].Wochentelefone.Add(start);
                        start = start.AddDays(7);
                        forwardedPositions.Remove(forwardedPosition);
                        index--;
                    }
                }

                position++;
                if (position >= praxen.Count)
                    position = 0;

                if (!praxen[position].AssignedPoints.Any(p => p.Dates.Any(d => d.Key >= start && d.Key <= start.AddDays(7))))
                {
                    praxen[position].Wochentelefone.Add(start);
                    start = start.AddDays(7);
                }
                else
                {
                    forwardedPositions.Add(position);
                }
            }
        }
    }
}
