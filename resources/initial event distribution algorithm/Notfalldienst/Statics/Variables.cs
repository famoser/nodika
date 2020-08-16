using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Notfalldienst.Models;

namespace Notfalldienst.Statics
{
    public static class Variables
    {
        public static List<DateTime> Feiertage = new List<DateTime>();
        public static List<int> History = new List<int>();
        public static List<Praxis> Praxen = new List<Praxis>();
        public static List<Punkt> Punkte = new List<Punkt>();

        public static int[] Minanzahl = new int[4] { 0, 0, 0, 0 };
        public static double[] Bonuspunkte = new double[4] { 2, 1.5, 1.2, 1 };
    }
}
