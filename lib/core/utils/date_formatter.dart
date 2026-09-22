import 'package:intl/intl.dart';

class DateFormatter {
  static String formatDateTime(DateTime dateTime) {
    return DateFormat('dd/MM/yyyy HH:mm').format(dateTime);
  }

  static String formatDate(DateTime dateTime) {
    return DateFormat('dd/MM/yyyy').format(dateTime);
  }

  static String tryFormatIso(String? isoString) {
    if (isoString == null) return 'N/A';
    try {
      final parsed = DateTime.parse(isoString).toLocal();
      return formatDateTime(parsed);
    } catch (_) {
      return isoString;
    }
  }
}
