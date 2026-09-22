import 'package:flutter/material.dart';
import '../../../../core/constants/app_colors.dart';

class PriorityBadge extends StatelessWidget {
  final String priority;

  const PriorityBadge({super.key, required this.priority});

  Color _getColor() {
    switch (priority.toLowerCase()) {
      case 'baja':
        return AppColors.priorityLow;
      case 'media':
        return AppColors.priorityMedium;
      case 'alta':
        return AppColors.priorityHigh;
      case 'crítica':
      case 'critica':
        return AppColors.priorityCritical;
      default:
        return AppColors.textSecondary;
    }
  }

  @override
  Widget build(BuildContext context) {
    final color = _getColor();

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: color.withAlpha(30),
        borderRadius: BorderRadius.circular(6),
      ),
      child: Text(
        'Prioridad $priority',
        style: TextStyle(
          color: color,
          fontSize: 11,
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }
}
