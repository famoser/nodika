using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Notfalldienst.Statics;

namespace Notfalldienst.Helpers
{
    public class DateTimeHelper
    {
        public static bool IsFeiertag(DateTime zeit)
        {
            foreach (var item in Variables.Feiertage)
            {
                if (zeit == item)
                {
                    return true;
                }
            }
            return false;
        }

        public static DateTime GetNextMonday(DateTime datum)
        {
            int daysUntilMonday = (((int)DayOfWeek.Monday - (int)datum.DayOfWeek + 7) % 7);
            if (daysUntilMonday > 0)
                return datum.AddDays(daysUntilMonday);
            return datum;
        }
    }
}
