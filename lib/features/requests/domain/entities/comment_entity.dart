import 'package:equatable/equatable.dart';

class CommentEntity extends Equatable {
  final int id;
  final String authorName;
  final String authorRole;
  final String content;
  final String createdAt;

  const CommentEntity({
    required this.id,
    required this.authorName,
    required this.authorRole,
    required this.content,
    required this.createdAt,
  });

  @override
  List<Object?> get props => [id, authorName, authorRole, content, createdAt];
}
