using System.Collections.Generic;
using Newtonsoft.Json;

namespace Notfalldienst.Helpers
{
    public static class SaveHelper
    {
        public static string SaveScore(Dictionary<int, double> praxenScore)
        {
            return JsonConvert.SerializeObject(praxenScore, Formatting.Indented);
        }

        public static string SaveHistory(List<int> praxen)
        {
            return JsonConvert.SerializeObject(praxen, Formatting.Indented);
        }

        public static Dictionary<int, double> RetrieveScore(string saveString)
        {
            return JsonConvert.DeserializeObject<Dictionary<int, double>>(saveString);
        }

        public static List<int> RetrieveHistory(string saveString)
        {
            return JsonConvert.DeserializeObject<List<int>>(saveString);
        }
    }
}
