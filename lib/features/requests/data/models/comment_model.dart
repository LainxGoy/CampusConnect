import '../../domain/entities/comment_entity.dart';

class CommentModel extends CommentEntity {
  const CommentModel({
    required super.id,
    required super.authorName,
    required super.authorRole,
    required super.content,
    required super.createdAt,
  });

  factory CommentModel.fromJson(Map<String, dynamic> json) {
    return CommentModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      authorName: json['author_name']?.toString() ?? json['authorName']?.toString() ?? 'Administrador',
      authorRole: json['author_role']?.toString() ?? json['authorRole']?.toString() ?? 'Personal',
      content: json['content']?.toString() ?? '',
      createdAt: json['created_at']?.toString() ?? json['createdAt']?.toString() ?? '',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'author_name': authorName,
      'author_role': authorRole,
      'content': content,
      'created_at': createdAt,
    };
  }
}
