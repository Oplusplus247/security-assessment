<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Invitation - {{ $factor->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .assessment-info {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .question-count {
            display: inline-block;
            background: #e3f2fd;
            color: #1976d2;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            margin: 10px 0;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 6px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
            transition: all 0.3s ease;
        }
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .instructions {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
        }
        .instructions h3 {
            margin-top: 0;
            color: #856404;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .expiry-notice {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin: 15px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎯 Assessment Invitation</h1>
            <p>You're invited to complete an important assessment</p>
        </div>
        
        <div class="content">
            <h2>Hello!</h2>
            
            <p>You have been invited to complete the <strong>{{ $factor->name }}</strong> assessment. Your input is valuable and will help improve our organization's readiness.</p>
            
            <div class="assessment-info">
                <h3>📋 Assessment Details</h3>
                <p><strong>Factor:</strong> {{ $factor->name }}</p>
                <div class="question-count">{{ $questions->count() }} Questions</div>
                <p><strong>Estimated Time:</strong> {{ ceil($questions->count() / 2) }}-{{ $questions->count() }} minutes</p>
            </div>

            @if($include_instructions)
            <div class="instructions">
                <h3>📝 Instructions</h3>
                <ul>
                    <li>Please answer all questions honestly and to the best of your knowledge</li>
                    <li>Each question is rated on a scale of 1-5 (1 = Strongly Disagree, 5 = Strongly Agree)</li>
                    <li>You can save your progress and return later if needed</li>
                    <li>Additional comments are welcome but not required</li>
                    <li>Your responses will be kept confidential</li>
                </ul>
            </div>
            @endif

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $assessment_url }}" class="cta-button">
                    🚀 Start Assessment
                </a>
            </div>

            <div class="expiry-notice">
                <strong>⏰ Important:</strong> This assessment link will expire on {{ $expires_at->format('F j, Y') }}. Please complete it before this date.
            </div>

            <p><strong>Need Help?</strong><br>
            If you have any questions about this assessment or experience technical difficulties, please contact our support team.</p>

            <hr style="margin: 30px 0; border: none; height: 1px; background: #e9ecef;">

            <p style="color: #666; font-size: 14px;">
                <strong>Assessment Preview - Questions Include:</strong><br>
                @foreach($questions->take(3) as $question)
                    • {{ Str::limit($question->question, 60) }}<br>
                @endforeach
                @if($questions->count() > 3)
                    • ... and {{ $questions->count() - 3 }} more questions
                @endif
            </p>
        </div>
        
        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>If you did not expect this assessment invitation, please contact your administrator.</p>
            @if(isset($tracking_id))
                <p style="font-size: 12px; color: #999;">Tracking ID: {{ $tracking_id }}</p>
            @endif
        </div>
    </div>
</body>
</html>