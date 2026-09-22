import '../../domain/entities/status_history_entity.dart';

class StatusHistoryModel extends StatusHistoryEntity {
  const StatusHistoryModel({
    required super.status,
    super.note,
    required super.changedBy,
    required super.timestamp,
  });

  factory StatusHistoryModel.fromJson(Map<String, dynamic> json) {
    return StatusHistoryModel(
      status: json['status']?.toString() ?? 'Pendiente',
      note: json['note']?.toString(),
      changedBy: json['changed_by']?.toString() ?? json['changedBy']?.toString() ?? 'Sistema',
      timestamp: json['timestamp']?.toString() ?? json['created_at']?.toString() ?? '',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'status': status,
      'note': note,
      'changed_by': changedBy,
      'timestamp': timestamp,
    };
  }
}
