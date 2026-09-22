import 'package:flutter/material.dart';

class AppColors {
  // Primarios Institucionales
  static const Color primary = Color(0xFF1E3A8A); // Azul institucional oscuro
  static const Color primaryLight = Color(0xFF3B82F6);
  static const Color secondary = Color(0xFF0D9488); // Teal / Innovación

  // Estados de Solicitud
  static const Color statusPending = Color(0xFFF59E0B); // Ámbar / Pendiente
  static const Color statusInProgress = Color(0xFF3B82F6); // Azul / En Proceso
  static const Color statusResolved = Color(0xFF10B981); // Verde / Resuelto
  static const Color statusClosed = Color(0xFF6B7280); // Gris / Cerrado
  static const Color statusCancelled = Color(0xFFEF4444); // Rojo / Cancelado

  // Prioridades
  static const Color priorityLow = Color(0xFF10B981);
  static const Color priorityMedium = Color(0xFFF59E0B);
  static const Color priorityHigh = Color(0xFFF97316);
  static const Color priorityCritical = Color(0xFFDC2626);

  // Fondos y Textos
  static const Color background = Color(0xFFF8FAFC);
  static const Color surface = Colors.white;
  static const Color textPrimary = Color(0xFF0F172A);
  static const Color textSecondary = Color(0xFF64748B);
  static const Color border = Color(0xFFE2E8F0);
  static const Color error = Color(0xFFDC2626);
}
