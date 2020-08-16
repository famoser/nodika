using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Notfalldienst.Statics;

namespace Notfalldienst.Models
{
    public class Punkt
    {
        public Punkt()
        {
            Dates = new Dictionary<DateTime, int>();
        }

        public double Score => Variables.Bonuspunkte[0]*Anzahl[0] + Variables.Bonuspunkte[1]*Anzahl[1] +
                               Variables.Bonuspunkte[2]*Anzahl[2] + Variables.Bonuspunkte[3]*Anzahl[3];

        //alle tage
        public int[] Anzahl = new int[4] { 0, 0, 0, 0 };

        //modiefied by verteiler
        public int[] TempAnzahl = new int[4] { 0, 0, 0, 0 };

        //zusätzliche Tage, die nicht alle haben
        public int[] Mehr = new int[4] { 0, 0, 0, 0 };

        //tage, die gutgeschrieben wurden wegen schlechtem score
        public int[] Weniger = new int[4] { 0, 0, 0, 0 };

        public Praxis Praxis { get; set; }

        public Dictionary<DateTime, int> Dates { get; set; }
    }
}
