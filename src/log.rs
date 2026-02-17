use crate::error::Error;

pub fn print_block(logs: Vec<String>) {
    println!("====================================");
    for log in logs {
        println!("{}", log);
    }
    println!("====================================");
}

pub fn print_error(err: Error) {
    print_block(vec![
        format!("error found on {}", err.error_on),
        format!("error found while {}", err.error_while),
        format!("{:#?}", err.error),
    ]);
}
