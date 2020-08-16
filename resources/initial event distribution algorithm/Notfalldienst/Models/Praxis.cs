using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;

namespace Notfalldienst.Models
{
    public class Praxis
    {
        public Praxis()
        {
            Wochentelefone = new List<DateTime>();
        }

        public int Id { get; set; }
        public string Praxisleiter { get; set; }
        public int Punkte { get; set; }

        public double LastYearScore { get; set; }
        public List<Punkt> AssignedPoints = new List<Punkt>();

        public List<DateTime> Wochentelefone { get; set; }
        
        public double ScorePerPoint
        {
            get
            {
                double sc = AssignedPoints.Sum(item => item.Score);
                sc += LastYearScore;

                sc = sc / AssignedPoints.Count;
                return sc;
            }
        }

        public double AbsoluteScorePerPoint
        {
            get
            {
                double sc = AssignedPoints.Sum(item => item.Score);

                sc = sc / AssignedPoints.Count;
                return sc;
            }
        }

        public int[] DayTypes()
        {
            int[] anz = new int[4];
            foreach (var item in AssignedPoints)
            {
                anz[0] += item.Anzahl[0];
                anz[1] += item.Anzahl[1];
                anz[2] += item.Anzahl[2];
                anz[3] += item.Anzahl[3];
            }
            return anz;
        }
    }
}
