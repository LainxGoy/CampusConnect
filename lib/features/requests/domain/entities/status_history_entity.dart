import 'package:equatable/equatable.dart';

class StatusHistoryEntity extends Equatable {
  final String status;
  final String? note;
  final String changedBy;
  final String timestamp;

  const StatusHistoryEntity({
    required this.status,
    this.note,
    required this.changedBy,
    required this.timestamp,
  });

  @override
  List<Object?> get props => [status, note, changedBy, timestamp];
}
