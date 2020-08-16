using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Notfalldienst.Models;

namespace Notfalldienst.Helpers
{
    public static class CloneHelper
    {
        public static List<Punkt> DeepClone(List<Punkt> toClone)
        {
            var res = new List<Punkt>();
            foreach (var punkt in toClone)
            {
                res.Add(new Punkt()
                {
                    Anzahl = new[]
                    {
                        punkt.Anzahl[0], punkt.Anzahl[1], punkt.Anzahl[2], punkt.Anzahl[3]
                    },
                    Mehr = new[]
                    {
                        punkt.Mehr[0], punkt.Mehr[1], punkt.Mehr[2], punkt.Mehr[3]
                    },
                    Weniger = new[]
                    {
                        punkt.Weniger[0], punkt.Weniger[1], punkt.Weniger[2], punkt.Weniger[3]
                    },
                    Praxis = punkt.Praxis
                });
            }
            return res;
        }
    }
}
